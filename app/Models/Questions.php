<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    protected $casts = [
    'options' => 'array',
    ];
    protected $table = 'questions';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        "exam_id",
        "question",
        "type",
        "options",
        "correct_answer",
        "explanation",
        "image",
        "order",
        "is_active"
    ];

    public function exams()
{
    return $this->belongsToMany(
        Exam::class,
        'exam_question'
    )->withPivot('order')->withTimestamps();
}
    public function exam(){
        return $this->belongsToMany(Exam::class, 'exam_id','exam_question', 'question_id', 'exam_id');
    }

}
