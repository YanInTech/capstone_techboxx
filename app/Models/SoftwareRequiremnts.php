<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareRequiremnts extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareRequiremntsFactory> */
    use HasFactory;

    protected $fillable = [
        'software_id',
        'cpu_min',
        'cpu_reco',
        'ram_min',
        'ram_reco',
        'gpu_min',
        'gpu_reco',
        'storage_min',
        'storgae_reco',
    ];
}
