<?php

namespace App\Services;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;

class CompatibilityService
{
    private array $caseSupportMap = [
        'ATX' => ['ATX', 'Micro-ATX', 'Mini-ITX'],
        'Micro-ATX' => ['Micro-ATX', 'Mini-ITX'],
        'Mini-ITX' => ['Mini-ITX'],
        'E-ATX' => ['E-ATX', 'ATX', 'Micro-ATX', 'Mini-ITX'],
    ];

    // CPU - MOTHERBOARD
    public function isCpuCompatiblewithMotherboard(Cpu $cpu, Motherboard $motherboard): bool
    {
        return $cpu->socket_type === $motherboard->socket_type;
    }

    // RAM - MOTHERBOARD
    public function isRamCompatiblewithMotherboard(Ram $ram, Motherboard $motherboard): bool
    {
        return $ram->ram_type === $motherboard->ram_type;
    }

    // GPU - CASE
    public function isGpuCompatiblewithCase(Gpu $gpu, PcCase $case): bool
    {
        return $gpu->length_mm <= $case->max_gpu_length_mm;
    }

    // COOLER - CPU AND CASE
    public function isCoolerCompatible(Cooler $cooler, Cpu $cpu, PcCase $case): bool
    {
        $socketCompatible = in_array($cpu->socket_type, $cooler->socket_compatibility);
        $heightCompatible = $cooler->height_mm <= $case->max_cooler_height_mm;

        return $socketCompatible && $heightCompatible;
    }

    // PSU - CPU + GPU
    public function isPsuEnough(Psu $psu, Cpu $cpu, Gpu $gpu): bool
    {
        $requiredPower = $cpu->tdp + $gpu->power_draw_watts + 100; // ADD BUFFER
        return $psu->wattage >= $requiredPower;
    }

    // MOTHERBOARD - CASE
    public function isMotherboardCompatiblewithCase(Motherboard $motherboard, PcCase $case): bool
    {
        $supported = $this->caseSupportMap[$case->form_factor_support] ?? [];

        return in_array($motherboard->form_factor, $supported);
    }

    // STORAGE - MOTHERBOARD
    public function isStorageCompatiblewithMotherboard(Motherboard $motherboard, Storage $storage): bool
    {
        switch (strtolower($storage->interface)) {
            case 'm.2':
            case 'nvme':
                return $motherboard->m2_slots > 0;

            case 'sata':
                return $motherboard->sata_ports > 0;
            
            default:
                return false;
        }
    }
}