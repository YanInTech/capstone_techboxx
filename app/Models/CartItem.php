<?php

namespace App\Models;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;
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
        'processed',
    ];

    public function shoppingCart() {
        return $this->belongsTo(ShoppingCart::class, 'shopping_cart_id');
    }

    public function checkout() {
        return $this->hasMany(Checkout::class);
    }

    public function motherboard() {
        return $this->belongsTo(Motherboard::class, 'product_id');
    }

    public function cpu() {
        return $this->belongsTo(Cpu::class, 'product_id');
    }

    public function case() {
        return $this->belongsTo(PcCase::class, 'product_id');
    }

    public function gpu() {
        return $this->belongsTo(Gpu::class, 'product_id');
    }

    public function psu() {
        return $this->belongsTo(Psu::class, 'product_id');

    }

    public function storage() {
        return $this->belongsTo(Storage::class, 'product_id');
    }

    public function ram() {
        return $this->belongsTo(Ram::class, 'product_id');
    }
    
    public function cooler() {
        return $this->belongsTo(Cooler::class, 'product_id');
    }

}
