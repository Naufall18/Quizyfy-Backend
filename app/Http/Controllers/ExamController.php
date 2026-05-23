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

    public function available(Request $request)
    {
        $user = $request->user();
        $available = Exam::with('category:id,name')
            ->where('status', 'aktif')
            ->whereDoesntHave('userExams', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(10);

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
        $exam = Exam::findOrFail($id);
        
        $results = ExamResult::where('exam_id', $id)
            ->with('user:id,name,email')
            ->latest()
            ->get();
            
        // Map the results to have score and passing_grade
        $mappedResults = $results->map(function ($result) use ($exam) {
            return [
                'user' => $result->user,
                'score' => (int) $result->score,
                'passing_grade' => $exam->kkm_score,
                'submitted_at' => $result->completed_at ? $result->completed_at->toIso8601String() : null,
            ];
        });
        
        return response()->json([
            'data' => $mappedResults
        ]);
    }

    /**
     * Get detailed statistics for an exam.
     * For web: exam analytics, difficulty distribution, etc
     */
    public function statistics($id)
    {
        $exam = Exam::with('questions')->findOrFail($id);
        
        // Get exam results
        $results = ExamResult::where('exam_id', $id)
            ->with('user')
            ->latest()
            ->get();
        
        // Calculate statistics
        $totalQuestions = $exam->questions->count();
        $totalSubmissions = $results->count();
        $passCount = $results->where('score', '>=', $exam->kkm_score)->count();
        $averageScore = $results->count() > 0 ? $results->avg('score') : 0;
        
        // Difficulty distribution
        $difficultyDistribution = $exam->questions
            ->groupBy('difficulty')
            ->map(function ($questions) {
                return [
                    'count' => $questions->count(),
                    'percentage' => round(($questions->count() / count($exam->questions)) * 100, 2)
                ];
            });
        
        // Type distribution
        $typeDistribution = $exam->questions
            ->groupBy('type')
            ->map(function ($questions) {
                return [
                    'count' => $questions->count(),
                    'percentage' => round(($questions->count() / count($exam->questions)) * 100, 2)
                ];
            });
        
        // Score distribution (grouped in ranges: 0-20, 20-40, etc)
        $scoreRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];
        
        foreach ($results as $result) {
            $score = $result->score;
            if ($score <= 20) $scoreRanges['0-20']++;
            elseif ($score <= 40) $scoreRanges['21-40']++;
            elseif ($score <= 60) $scoreRanges['41-60']++;
            elseif ($score <= 80) $scoreRanges['61-80']++;
            else $scoreRanges['81-100']++;
        }
        
        $response = [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration_minutes' => $exam->duration_minutes,
                'kkm_score' => $exam->kkm_score,
            ],
            'questions' => [
                'total' => $totalQuestions,
                'by_type' => $typeDistribution->toArray(),
                'by_difficulty' => $difficultyDistribution->toArray(),
            ],
            'submissions' => [
                'total' => $totalSubmissions,
                'pass_count' => $passCount,
                'fail_count' => $totalSubmissions - $passCount,
                'pass_rate' => $totalSubmissions > 0 ? round(($passCount / $totalSubmissions) * 100, 2) : 0,
                'average_score' => round($averageScore, 2),
                'highest_score' => $results->count() > 0 ? $results->max('score') : 0,
                'lowest_score' => $results->count() > 0 ? $results->min('score') : 0,
            ],
            'score_distribution' => $scoreRanges,
        ];
        
        return BaseResponse::OK($response, 'Exam statistics retrieved successfully');
    }
}