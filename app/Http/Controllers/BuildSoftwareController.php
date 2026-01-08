<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\BuildCategory;
use App\Models\Software;

class BuildSoftwareController extends Controller
{

public function index()
    {
        // 1️⃣ Get session components
        $sessionComponents = session('selected_components', []);

        // 2️⃣ Map each type to its model
        $modelMap = [
            'cpu' => \App\Models\Hardware\Cpu::class,
            'gpu' => \App\Models\Hardware\Gpu::class,
            'ram' => \App\Models\Hardware\Ram::class,
            'motherboard' => \App\Models\Hardware\Motherboard::class,
            'case' => \App\Models\Hardware\PcCase::class,
            'psu' => \App\Models\Hardware\Psu::class,
            'ssd' => \App\Models\Hardware\Storage::class,
            'hdd' => \App\Models\Hardware\Storage::class,
            'cooler' => \App\Models\Hardware\Cooler::class,
        ];

        // 3️⃣ Build the selectedComponents array
        $selectedComponents = [];
        $totalRam = 0;
        $totalStorage = 0;

        foreach ($sessionComponents as $type => $data) {
            if (isset($modelMap[$type]) && isset($data['componentId'])) {
                $component = $modelMap[$type]::find($data['componentId']);
                if ($component) {
                    $selectedComponents[$type] = $component;
                    
                    // Calculate total RAM and storage
                    if ($type === 'ram' && $component->total_capacity_gb) {
                        $totalRam = $component->total_capacity_gb;
                    }
                    if (($type === 'ssd' || $type === 'hdd') && $component->capacity_gb) {
                        $totalStorage += $component->capacity_gb;
                    }
                }
            }
        }

        $fullComponents = [
            'ram' => isset($selectedComponents['ram']) ? [
                'total_capacity_gb' => $selectedComponents['ram']->total_capacity_gb,
                'brand' => $selectedComponents['ram']->brand,
                'model' => $selectedComponents['ram']->model,
                'speed_mhz' => $selectedComponents['ram']->speed_mhz,
                'ram_type' => $selectedComponents['ram']->ram_type,
            ] : null,

            'ssd' => isset($selectedComponents['ssd']) ? [
                'capacity_gb' => $selectedComponents['ssd']->capacity_gb,
                'brand' => $selectedComponents['ssd']->brand,
                'model' => $selectedComponents['ssd']->model,
            ] : null,

            'hdd' => isset($selectedComponents['hdd']) ? [
                'capacity_gb' => $selectedComponents['hdd']->capacity_gb,
                'brand' => $selectedComponents['hdd']->brand,
                'model' => $selectedComponents['hdd']->model,
            ] : null,
        ];

        // 4️⃣ Get build categories and filter compatible software
        $buildCategories = BuildCategory::all();
        $allSoftwares = Software::all();
        
        // Filter compatible software
        $compatibleSoftwares = $allSoftwares->filter(function ($software) use ($totalRam, $totalStorage) {
            $minRam = (int) $software->ram_min;
            $minStorage = (int) $software->storage_min;
            
            // Check compatibility (same logic as your frontend)
            return $totalRam >= $minRam && $totalStorage >= $minStorage;
        });

        // 5️⃣ Return view with compatible software
        return view('buildExtSoft', [
            'selectedComponents' => $selectedComponents,
            'fullComponents' => $fullComponents,
            'buildCategories' => $buildCategories,
            'softwares' => $compatibleSoftwares, // Now only contains compatible software
        ]);
    }

    
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
