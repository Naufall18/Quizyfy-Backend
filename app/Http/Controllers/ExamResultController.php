<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamResultRequest;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('viewAny', ExamResult::class)) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $result = ExamResult::with(['exam:id,titles', 'user:id,name', 'teacher:id,name', 'userExam:id,user_id,exam_id'])
            ->when($request->query('exam_id'), fn($q) => $q->where('exam_id', $request->query('exam_id')))
            ->when($request->query('user_id'), fn($q) => $q->where('user_id', $request->query('user_id')))
            ->latest()
            ->paginate($request->query('per_page', 10));

        return response()->json($result);
    }

    public function store(StoreExamResultRequest $request)
    {
        if (Gate::denies('create', ExamResult::class)) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $data = $request->validated();
        
        // Fixed: Proper array syntax for completed_at
        $data['completed_at'] = now();

        $examResult = ExamResult::create($data);

        return response()->json([
            'message' => 'Exam result created successfully',
            'exam_result' => $examResult
        ], 201);
    }

    public function show(ExamResult $examResult)
    {
        if (Gate::denies('view', $examResult)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $examResult->load(['exam', 'user', 'teacher', 'userExam']);
        return response()->json($examResult);
    }
}