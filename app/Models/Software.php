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
        'os_min',
        'cpu_min',
        'cpu_reco',
        'ram_min',
        'ram_reco',
        'gpu_min',
        'gpu_reco',
        'storage_min',
        'storage_reco',
    ];

    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }

}
