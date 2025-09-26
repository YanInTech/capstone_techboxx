<?php

namespace App\Models\Hardware;

use App\Models\Hardware\PcCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PcCaseFrontUsbPorts extends Model
{
    /** @use HasFactory<\Database\Factories\PcCaseFrontUsbPortsFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'pc_case_id',
        'usb_3_0_type_A',
        'usb_2_0',
        'usb_c',
        'audio_jacks',
    ];

    public function pcCase() {
        return $this->belongsTo(PcCase::class);
    }
}
