<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'plan_type',
        'status',
        'payment_status',
        'start_date',
        'end_date',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'start_date'     => 'datetime',
        'end_date'       => 'datetime',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
