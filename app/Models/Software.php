<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareFactory> */
    use HasFactory;
    // use SoftDeletes;

    protected $fillable = [
        'name',
        'build_category_id',
    ];

    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }
}
