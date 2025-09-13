<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'name',
        'supplier_id'
    ];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
