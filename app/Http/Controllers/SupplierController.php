<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //
    public function index() {
        $suppliers = Supplier::with('brands')
            ->orderByRaw("CASE WHEN is_active = 0 THEN 1 ELSE 0 END")
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('staff.supplier', compact('suppliers'));
    }

    public function storeSupplier(Request $request) {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $validate['is_active'] = true;

        Supplier::create($validate);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier added',
            'type' => 'success',
        ]); 
    }

    public function storeBrand(Request $request) {
        $validate = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
        ]);

        Brand::create($validate);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Brand added',
            'type' => 'success',
        ]); 
    }

    public function update(Request $request, string $id)  {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        foreach ($request->brands as $brandData) {
            if (!empty(trim($brandData['name']))) {
                Brand::where('id', $brandData['id'])->update([
                    'name' => $brandData['name'],
                ]);
            }
        }

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier details updated',
            'type' => 'success',
        ]); 
    }

    public function delete($id) {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'is_active' => false
        ]);

        $supplier->brands()->delete();

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier status has been inactive',
            'type' => 'success',
        ]);
    }

    public function restore($id) {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'is_active' => true
        ]);

        $supplier->brands()->restore();

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier status has been inactive',
            'type' => 'success',
        ]);
    }
}
