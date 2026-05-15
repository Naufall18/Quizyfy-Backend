<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
        public $incrementing = true;
    protected $fillable = ['repository_id', 'path','content', 'language', 'analyzed' ];

    public function repository(){
        return $this->belongsTo(Repository::class);
    }
    public function review(){
        return $this ->hasOne(Review::class);
    }
}
