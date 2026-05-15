<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
 use HasFactory;

    protected $fillable = [
        'user_exam_id',
        'exam_id',
        'user_id',
        'teacher_id',
        'completed_at',
        'total_question',
        'correct_answer',
        'wrong_answer',
        'unanswered',
        'score',
        'percentage',
        'is_passed',
        'detailed_answer',
        'time_spent_minutes',
        'feedback',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'detailed_answer' => 'array',
        'is_passed' => 'boolean',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    // Relationships
    public function userExam()
    {
        return $this->belongsTo(UserExam::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

}