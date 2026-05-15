<?php

namespace App\Http\Controllers;
use App\Http\Requests\FinishUserExamRequest;
use App\Http\Requests\StartUserExamRequest;
use App\Models\Exam;
use App\Models\UserExam;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


class UserExamController extends Controller
{
    public function start(StartUserExamRequest $request, Exam $exam){
        $user = Auth::user();
        if($exam ->status !== 'aktif' || ($exam->start_time && Carbon::now()->lt($exam->start_time)) || ($exam->end_time && Carbon::now()->gt($exam->end_time))){
            return response()->json(['message' => 'Ujian tidak tersedia'],403);
        }
        $last =UserExam::where('user_id', $user->id)->orderBy('attempt_number','desc')->first();

        $attemptnumber = UserExam::where('user_id', $user->id)->where('exam_id', $exam->id)->orderBy('attempt_number', 'desc')->first();
        $deadline = Carbon::now()->addMinute($exam->duration_minutes);
        $userExam = UserExam::create([
            'user_id'=> $user->id,
            'exam_id' => $exam ->id,
            'started_at' => Carbon::now(),
            'deadline' => $deadline,
            'status' => 'in_progress',
            'attempt_number' =>$attemptnumber,
        ]);
        return response()->json([
            'message'=> 'Ujian dimulai',
            'user_exam'=> $userExam,
            'remaining_seconds' => Carbon::now()->diffInSeconds($deadline,false)
        ],201);


    }
    public function status(Request $request, Exam $exam){
        
        $user = Auth::user();
        $userExam = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();
    
        $now = Carbon::now();
        $remaining = $userExam->deadline ? $now->diffInSeconds($userExam->deadline, false) : null;
    
        if ($remaining !== null && $remaining <= 0) {
            $userExam->update(['status' => 'time_up', 'finished_at' => $now]);
            return response()->json(['status' => 'time_up', 'remaining_seconds' => 0]);
        }
    
        return response()->json([
            'status' => $userExam->status,
            'remaining_seconds' => $remaining,
        ]);
    }

    public function finish(FinishUserExamRequest $request, Exam $exam){
        $user = Auth::user();
        $data = $request->validated();

        $userExam = UserExam::where('user_id', $user->id)
        ->where('exam_id', $exam->id)->where('status', 'in_progress')
        ->latest()->firstOrFail();

        $userExam->update([
            'finished_at' => Carbon::now(),
            'status' => 'completed',
            'score' => $data['score'],
            'correct_answers' => $data['correct_answer'],
            'wrong_answers' => $data['wrong_answers'],
            'answers' => $data['answers'],
        ]);
        return response()->json([
            'message' => 'Ujian telah selesai',
            'user_exam' => $userExam

        ]);
    }
}
