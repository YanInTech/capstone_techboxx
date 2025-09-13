<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\ComponentDetailsController;
use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Psu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;

class PsuController extends Controller
{
    // FETCHING DATA FRO DROPDOWNS
    public function getPsuSpecs()
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => Brand::select('id', 'name', 'supplier_id')->get(),
            'efficiency_ratings' => ['80 PLUS Bronze', '80 PLUS Gold', '80 PLUS Titanium', ],
            'modulars' => ['Non-Modular', 'Semi-Modular', 'Fully Modular', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),

        ];
    }

    public function getFormattedPsus()
    {
        $psus = Psu::all();

        $psuSales = DB::table('user_builds')
                ->select('psu_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('psu_id')
                ->pluck('sold_count', 'psu_id');

        // FORMATTING THE DATAS
        $psus->each(function ($psu) use ($psuSales) {
            $psu->price_display = '₱' . number_format($psu->price, 2);
            $psu->label = "{$psu->brand} {$psu->model}";
            $psu->component_type = 'psu';

            
            $psu->sold_count = $psuSales[$psu->id] ?? 0;
        });

        return $psus;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // Validate the request data
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'wattage' => 'required|integer|max:255',
            'efficiency_rating' => 'required|string|max:255',
            'modular' => 'required|string|max:255',
            'pcie_connectors' => 'required|integer|max:255',
            'sata_connectors' => 'required|integer|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:20480',
            'build_category_id' => 'required|exists:build_categories,id',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
            $validated['image'] = $validated['image']->storeAs('psu', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('psu', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // dd($request->all()); 
        

        Psu::create($validated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'PSU added',
            'type' => 'success',
        ]); 

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

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $psu = Psu::findOrFail($id);
        // dd($request->all());

        $psu->update([
            'brand' => $request->brand,
            'model' => $request->model,
            'wattage' => $request->wattage,
            'efficiency_rating' => $request->efficiency_rating,
            'modular' => $request->modular,
            'pcie_connectors' => $request->pcie_connectors,
            'sata_connectors' => $request->sata_connectors,
            'price' => $request->price,
            'stock' => $request->stock,
            'build_category_id' => $request->build_category_id,
        ]); 

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'PSU updated',
            'type' => 'success',
        ]);

    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

   

}
