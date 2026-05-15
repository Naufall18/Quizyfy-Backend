<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAnswer\StoreUserAnswerRequest;
use App\Http\Requests\UserAnswer\UpdateUserAnswer;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class UserAnswerController extends Controller
{
    public function store(StoreUserAnswerRequest $request){
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['answered_at'] = now();
        $answer = UserAnswer::updateOrCreate([
            'user_id' =>$data['user_id'],
            'exam_id' => $data['exam_id'],
            'question_id' => $data['question_id'],

        ],$data);
        return response()->json($answer,201);
    }
    public function update(UpdateUserAnswer $request, UserAnswer $userAnswer){
        $userAnswer->update($request->validated());
        return response()->json($userAnswer);
    }
}
