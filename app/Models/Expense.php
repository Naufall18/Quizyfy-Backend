<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'date',
        'category_id',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    
}
