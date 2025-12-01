<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderedBuild extends Model
{
    /** @use HasFactory<\Database\Factories\OrderedBuildFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'created_at',
        'user_build_id',
        'status',
        'user_id',
        'payment_status',
        'payment_method',
        'pickup_status',
        'pickup_date',
        'is_downpayment',
        'downpayment_amount',
        'remaining_balance',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
    ];

    public function userBuild() {
        return $this->belongsTo(UserBuild::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice() {
        return $this->hasOne(Invoice::class, 'build_id');
    }
}
