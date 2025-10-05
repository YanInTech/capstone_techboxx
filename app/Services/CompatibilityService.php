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
<<<<<<< HEAD
=======
use App\Models\Hardware\SupportedCpu;
>>>>>>> 0bfcf14 (Admin Dashbord alignment fontsize update)

class CompatibilityService
{
    private array $caseSupportMap = [
        'ATX' => ['ATX', 'Micro-ATX', 'Mini-ITX'],
        'Micro-ATX' => ['Micro-ATX', 'Mini-ITX'],
        'Mini-ITX' => ['Mini-ITX'],
        'E-ATX' => ['E-ATX', 'ATX', 'Micro-ATX', 'Mini-ITX'],
    ];

<<<<<<< HEAD
    // CPU - MOTHERBOARD
    public function isCpuCompatiblewithMotherboard(Cpu $cpu, Motherboard $motherboard): bool
    {
        return $cpu->socket_type === $motherboard->socket_type;
=======

    // CPU - MOTHERBOARD
    public function isCpuCompatiblewithMotherboard(Cpu $cpu, Motherboard $motherboard,SupportedCpu $supported_cpu)
    {
        $results = ['errors' => [], 'warnings' => []];
        if ($cpu->socket_type !== $motherboard->socket_type) {
            $results['errors'][]= "❌CPU and motherboard socket_type is incompatible.";
        }
        //motherboard supports cpu fallback
        if (!empty($supported_cpu->cpuID)) {
            // Compare names
            $cpuArray = array_map('trim', explode(',', $cpuList));
            foreach ($cpuList as $supportedCpu) {
                if (stripos($supportedCpu['Name'], $cpu->model_name) == false) {
                    return $results;
                }
            }
           $results['errors'][]= "❌Motherboard Doesn't Support CPU";
        }
        return $results;
>>>>>>> 0bfcf14 (Admin Dashbord alignment fontsize update)
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