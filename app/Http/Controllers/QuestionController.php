<?php

namespace App\Http\Controllers;


use App\Models\Exam;
use App\Models\Questions;
use Illuminate\Http\Request;
use App\Helpers\BaseResponse;
use App\Services\BankSoalService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BankRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\QuestionsRequest;
use Symfony\Component\Console\Question\Question;
use App\Services\Interface\QuestionServiceInteface;

class QuestionController extends Controller
{
    protected $questionService;
    protected BankSoalService $bank;
    public function __construct(BankSoalService $bank)
    {
        $this->bank = $bank;
    }

    public function index(Request $request)
    {
        $search   = $request->query('search');
        $category = $request->query('category_id');
        $type     = $request->query('type'); // pg|essay|true_false
        $perPage  = min((int) $request->query('per_page', 15), 100);
        $guruId   = auth()->id(); // hanya ambil soal milik guru ini

        $query = Questions::where('created_by', $guruId);

        if ($search) {
            $query->where('question', 'like', "%{$search}%");
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $questions = $query->latest()->paginate($perPage);

        return BaseResponse::OK($questions, $questions->total() > 0
            ? 'Soal berhasil diambil'
            : 'Belum ada soal di bank soal');
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
        return response()->json(['message' => 'Questions attached to exam successfully']);
    }
    public function detach(Exam $exam, $questionId): JsonResponse
    {

        if (! $exam->bankQuestions()->where('question_id', $questionId)->exists()) {
            return response()->json([
                'message' => "Question dengan ID {$questionId} Tidak tersambung"
            ], 404);
        }

        $exam->bankQuestions()->detach($questionId);

        return response()->json([
            'message' => "Question {$questionId} Diputuskan dengan sukses"
        ], 200);
    }
    public function bank(BankRequest $request)
    {
        $data = $request->validated();

        $filters = ['exam_id' => $data['exam_id'] ?? null, 'category_id' => $data['category_id'] ?? null];
        $search = $data['search'] ?? null;
        $shuffle = isset($data['shuffle']) && $data['shuffle'] == '1';
        $perPage = $data['per_page'] ?? 15;
        $paginator = $this->bank->list($filters, $search, $shuffle, $perPage);
        return response()->json($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuestionsRequest $request)
    {
        $validated = $request->validated();

        // Pastikan soal terikat ke guru yang membuat
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('questions', 'public');
        }

        $question = Questions::create($validated);

        return BaseResponse::Created($question, 'Soal berhasil ditambahkan ke bank soal');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = Questions::where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$question) {
            return BaseResponse::NotFound('Soal tidak ditemukan');
        }

        return BaseResponse::OK($question, 'Soal berhasil diambil');
    }

    /**
     * Update soal — hanya pemilik yang boleh mengubah.
     */
    public function update(QuestionsRequest $request, string $id)
    {
        $question = Questions::where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$question) {
            return BaseResponse::NotFound('Soal tidak ditemukan atau bukan milik Anda');
        }

        $validated = $request->validated();

        if (isset($validated['options']) && is_array($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('questions', 'public');
        }

        $question->update($validated);

        return BaseResponse::OK($question->fresh(), 'Soal berhasil diperbarui');
    }

    /**
     * Hapus soal — hanya pemilik yang boleh menghapus.
     * Guard: tidak bisa hapus jika masih terhubung ke ujian aktif.
     */
    public function destroy(string $id)
    {
        $question = Questions::where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$question) {
            return BaseResponse::NotFound('Soal tidak ditemukan atau bukan milik Anda');
        }

        // Cek apakah masih dipakai di ujian aktif
        $usedInActiveExam = $question->exams()
            ->where('status', 'aktif')
            ->exists();

        if ($usedInActiveExam) {
            return BaseResponse::BadRequest(
                'Soal tidak dapat dihapus karena sedang digunakan dalam ujian aktif.'
            );
        }

        $question->delete();

        return BaseResponse::NoContent();
    }
