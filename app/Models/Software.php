<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Software extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'build_category_id',
    ];

    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }

    public function specs() {
        return $this->hasOne(SoftwareRequirement::class);
    }
}
