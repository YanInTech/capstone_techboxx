<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareRequirement extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareRequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'software_id',
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

    public function software() {
        return $this->belongsTo(Software::class);
    }
}
