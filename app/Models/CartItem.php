<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    protected $fillable = [
        'shopping_cart_id',
        'product_id',
        'product_type',
        'quantity',
        'total_price',
    ];

    public function shoppingCart() {
        return $this->belongsTo(ShoppingCart::class);
    }

    public function checkout() {
        return $this->belongsTo(Checkout::class);
    }
}
