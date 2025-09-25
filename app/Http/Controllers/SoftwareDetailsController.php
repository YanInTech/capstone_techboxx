<?php

namespace App\Http\Controllers;

use App\Models\BuildCategory;
use App\Models\Software;
use App\Models\SoftwareRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SoftwareDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $softwares = Software::paginate(8);
        $buildCategories = BuildCategory::select('id', 'name')->get()->toArray();
        return view('staff.softwaredetails', compact('buildCategories', 'softwares'));

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'build_category_id' => 'required|exists:build_categories,id',
        ]);

        // Handle image upload
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon');
            $filename = time() . '_' . Str::slug(pathinfo($validated['icon']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['icon']->getClientOriginalExtension();
            $validated['icon'] = $validated['icon']->storeAs('softwareIcon', $filename, 'public');
        } else {
            $validated['icon'] = null;
        }

        $software = Software::create($validated);

        $specs = $request->validate([
            'os_min' => 'nullable|string|max:255',
            'cpu_min' => 'nullable|string|max:255',
            'gpu_min' => 'nullable|string|max:255',
            'ram_min' => 'nullable|integer',
            'storage_min' => 'nullable|integer',
            'cpu_reco' => 'nullable|string|max:255',
            'gpu_reco' => 'nullable|string|max:255',
            'ram_reco' => 'nullable|integer',
            'storage_reco' => 'nullable|integer',
        ]);

        $specs['software_id'] = $software->id;

        // dd($request->all());
        SoftwareRequirement::create($specs);

        return redirect()->route('staff.software-details')->with([
            'message' => 'Software added',
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
