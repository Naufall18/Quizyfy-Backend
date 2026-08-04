<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\ExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Helpers\BaseResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\UserExam;
use App\Models\ExamResult;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['category:id,name', 'creator:id,name,email'])->latest()->paginate(10);
        return response()->json($exams);
    }

    /**
     * Daftar ujian milik guru yang sedang login — dengan filter + pagination.
     * GET /guru/exams?status=aktif&page=1&per_page=15&search=bab
     */
    public function guruIndex(Request $request): JsonResponse
    {
        $guruId      = auth()->id();
        $status      = $request->query('status');
        $search      = $request->query('search');
        $categoryId  = $request->query('category_id');
        $perPage     = min((int) $request->query('per_page', 15), 100);

        $query = Exam::with(['category:id,name'])
            ->where('created_by', $guruId);

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('titles', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $exams = $query->withCount('questions')->latest()->paginate($perPage);

        return BaseResponse::OK($exams, 'Daftar ujian berhasil diambil');
    }

    public function available(Request $request)
    {
        $user       = $request->user();
        $categoryId = $request->query('category_id');

        $query = Exam::with('category:id,name')
            ->where('status', 'aktif')
            ->whereDoesntHave('userExams', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $available = $query->latest()->paginate(10);

        return response()->json($available);
    }

    public function examJoin(Request $request)
    {
        $user = $request->user();
        $token = $request->input('token');
        $exam = Exam::where('token', $token)->where('status', 'aktif')->first();

        if (!$exam) {
            return response()->json(['message' => 'Token salah atau ujian tidak aktif'], 404);
        }

        $now = Carbon::now();

        if ($now->lt($exam->start_time)) {
            return response()->json(['message' => 'Ujian belum dibuka'], 403);
        }
        if ($now->gt($exam->end_time)) {
            return response()->json(['message' => 'Ujian telah berakhir'], 403);
        }

        $already = UserExam::where([
            ['exam_id', $exam->id],
            ['user_id', $user->id]
        ])->exists();

        if ($already) {
            return response()->json(['message' => 'Anda sudah tergabung diujian ini'], 409);
        }

        $userExam = UserExam::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => Carbon::now(),
            'deadline' => Carbon::now()->addMinutes($exam->duration_minutes),
            'status' => 'in_progress'
        ]);

        $questions = $exam->questions()
            ->where('is_active', true)
            ->when($exam->shuffle_question, fn($q) => $q->inRandomOrder())
            ->get();

        return response()->json([
            'message' => 'Berhasil bergabung ujian',
            'exam' => $exam->only(['id', 'titles', 'duration_minutes', 'start_time', 'end_time']),
            'user_exam' => $userExam,
            'questions' => $questions
        ], 201);
    }

    public function attachToExam(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        foreach ($data['question_ids'] as $qid) {
            DB::table('exam_question')->insert([
                'exam_id' => $data['exam_id'],
                'question_id' => $qid,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['message' => 'Pertanyaan telah berhasil dihubungkan']);
    }

    // Fixed: Proper PATCH method with correct parameter type
    public function partialUpdate(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        if (Gate::denies('update', $exam)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'titles' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:draft,aktif,archived',
            'shuffle_question' => 'sometimes|boolean',
            'shuffle_option' => 'sometimes|boolean',
            'show_result' => 'sometimes|boolean',
            'max_attempts' => 'sometimes|integer|min:1',
        ]);

        $exam->update($validated);

        return response()->json([
            'message' => 'Ujian berhasil diperbarui sebagian',
            'exam' => $exam
        ]);
    }

    public function store(ExamRequest $request)
    {
        $validated = $request->validated();

        $validated['token'] = Str::upper(Str::random(6));
        $validated['created_by'] = Auth::user()->id;
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['shuffle_question'] = $request->boolean('shuffle_question');
        $validated['shuffle_option'] = $request->boolean('shuffle_option');
        $validated['show_result'] = $request->boolean('show_result');
        $validated['max_attempts'] = $validated['max_attempts'] ?? 1;

        $exam = Exam::create($validated);
        return response()->json(['message' => 'Ujian telah dibuat', 'exam' => $exam], 201);
    }

    // Fixed: Proper parameter type
    public function show($id)
    {
        $exam = Exam::with(['category', 'creator', 'questions'])->findOrFail($id);
        return response()->json($exam);
    }

    // Fixed: Proper parameter type
    public function update(UpdateExamRequest $request, $id)
    {
        $exam = Exam::findOrFail($id);

        if (Gate::denies('update', $exam)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $exam->update($validated);
        return response()->json(['message' => 'Ujian telah diperbarui', 'exam' => $exam]);
    }

    // Fixed: Proper parameter type
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);

        if (Gate::denies('delete', $exam)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $exam->delete();
        return response()->json(['message' => 'Ujian berhasil dihapus']);
    }

    public function results($id)
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $results = UserExam::where('exam_id', $id)
            ->whereIn('status', ['completed', 'time_up'])
            ->with('user:id,name,email')
            ->latest('finished_at')
            ->get()
            ->map(fn($ue) => [
                'id'             => $ue->id,
                'user'           => $ue->user,
                'score'          => $ue->score ?? 0,
                'total_score'    => 100,
                'correct_answers' => $ue->correct_answers ?? 0,
                'wrong_answers'  => $ue->wrong_answers ?? 0,
                'passed'         => ($ue->score ?? 0) >= ($exam->kkm_score ?? 70),
                'submitted_at'   => $ue->finished_at,
            ]);

        return BaseResponse::OK($results, 'Hasil ujian berhasil diambil');
    }

    /**
     * Statistik ringkas satu ujian (rata-rata nilai, % lulus, dll).
     * GET /guru/exams/{exam}/statistics
     */
    public function statistics($id): JsonResponse
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $userExams = UserExam::where('exam_id', $id)
            ->whereIn('status', ['completed', 'time_up'])
            ->get();

        $total   = $userExams->count();
        $passed  = $userExams->filter(fn($ue) => ($ue->score ?? 0) >= ($exam->kkm_score ?? 70))->count();
        $avgScore = $total > 0 ? round($userExams->avg('score'), 1) : 0;
        $maxScore = $total > 0 ? $userExams->max('score') : 0;
        $minScore = $total > 0 ? $userExams->min('score') : 0;

        return BaseResponse::OK([
            'total_peserta'   => $total,
            'lulus'           => $passed,
            'tidak_lulus'     => $total - $passed,
            'pass_rate'       => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'rata_rata_nilai' => $avgScore,
            'nilai_tertinggi' => $maxScore,
            'nilai_terendah'  => $minScore,
            'kkm'             => $exam->kkm_score ?? 70,
        ], 'Statistik ujian berhasil diambil');
    }

    /**
     * Toggle status ujian antara aktif dan draft.
     * PATCH /guru/exams/{exam}/toggle
     */
    public function toggleStatus($id): JsonResponse
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        // Toggle: aktif → draft, selainnya → aktif
        $newStatus = $exam->status === 'aktif' ? 'draft' : 'aktif';
        $exam->update(['status' => $newStatus]);

        return BaseResponse::OK(
            ['status' => $newStatus, 'exam_id' => $exam->id],
            $newStatus === 'aktif'
                ? 'Ujian berhasil diaktifkan'
                : 'Ujian berhasil dinonaktifkan'
        );
    }
}
