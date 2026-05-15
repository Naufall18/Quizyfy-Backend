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
        $search = $request->query('search');

        $query = Questions::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', '%' . $search . '%')
                    ->orWhereHas('exam', function ($q2) use ($search) {
                        $q2->where('titles', 'like', '%' . $search . '%');
                    }); 
            });
        }

        $questions = $query->paginate(10);
        return BaseResponse::OK($questions, $questions->count() > 0 ? 'Question Retrived successfully' : 'No questions found');
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


        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('questions', 'public');
        }


        $question = Questions::create($validated);

        return BaseResponse::Created($question, 'Question created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Questions::find($id);

        if (!$data) {
            return BaseResponse::NotFound('Question not found');
        }

        return BaseResponse::OK($data, 'Question retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuestionsRequest $request, string $id)
    {
        $question = Questions::find($id);

        if (!$question) {
            return BaseResponse::NotFound('Question not found');
        }

        $validated = $request->validated();

        if (isset($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        $question->update($validated);

        return BaseResponse::OK($question, 'Question updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = Questions::find($id);
        if (!$question) {
            return BaseResponse::NotFound('Question not found');
        }
        $question->delete();

        return BaseResponse::NoContent();
    }
}
