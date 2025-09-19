<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'payment_method',
        'status',
    ];

    // 🔗 Relation to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 Relation to order items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 🔗 Relation to payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
