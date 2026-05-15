<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public $incrementing = true;

    protected $fillable = [
        'titles', 'description', 'token', 'category_id', 'created_by',
        'start_time', 'end_time', 'duration_minutes', 'total_questions',
        'kkm_score', 'status', 'shuffle_question', 'shuffle_option',
        'show_result', 'max_attempts', 'instructions',
    ];

    // ─── Relationships ───────────────────────────

    public function bankQuestions()
    {
        return $this->belongsToMany(
            Questions::class,
            'exam_question'
        )->withPivot('order')->withTimestamps();
    }

    public function userExams()
    {
        return $this->hasMany(UserExam::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Questions::class, 'exam_question', 'exam_id', 'question_id');
    }

    public function usersTaken()
    {
        return $this->belongsToMany(User::class, 'user_exams');
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}
