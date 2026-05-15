<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = ['user_id', 'repo_name','repo_url', 'description', 'retrieved_at'];

    public function file(){
 return $this->hasMany(File::class);
    }
    public function user(){
 return $this->belongsTo(User::class);
    }
}
