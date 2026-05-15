<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    
    protected $fillable = [
        'exam_id',
        'user_id',
        'question_id',
        'answer',
        'is_correct',
    ]
    ;
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function exam(){
        return $this->belongsTo(Exam::class);
    }
    public function question(){
        return $this->belongsTo(Questions::class);
    }
}
