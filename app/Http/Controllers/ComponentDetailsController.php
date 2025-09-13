<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Components\CaseController;
use App\Http\Controllers\Components\CoolerController;
use App\Http\Controllers\Components\CpuController;
use App\Http\Controllers\Components\GpuController;
use App\Http\Controllers\Components\MoboController;
use App\Http\Controllers\Components\PsuController;
use App\Http\Controllers\Components\RamController;
use App\Http\Controllers\Components\StorageController;
use Illuminate\Http\Request;
use App\Services\GoogleDriveUploader;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage as FacadesStorage;

class ComponentDetailsController extends Controller
{
    public function getAllFormattedComponents()
    {
        return collect([
            ...app(CpuController::class)->getFormattedCpus(),
            ...app(MoboController::class)->getFormattedMobos(),
            ...app(GpuController::class)->getFormattedGpus(),
            ...app(CaseController::class)->getFormattedCases(),
            ...app(PsuController::class)->getFormattedPsus(),
            ...app(RamController::class)->getFormattedRams(),
            ...app(StorageController::class)->getFormattedStorages(),
            ...app(CoolerController::class)->getFormattedCoolers(),
        ])->sortBy([
                fn ($component) => is_null($component['deleted_at'])  ? 0 : 1,
                fn ($component) => -strtotime($component['created_at']),
            ])
          ->values();
        }

    public function getAllSpecs()
    {
        return [
            'moboSpecs' => app(MoboController::class)->getMotherboardSpecs(),
            'gpuSpecs' => app(GpuController::class)->getGpuSpecs(),
            'caseSpecs' => app(CaseController::class)->getCaseSpecs(),
            'psuSpecs' => app(PsuController::class)->getPsuSpecs(),
            'ramSpecs' => app(RamController::class)->getRamSpecs(),
            'storageSpecs' => app(StorageController::class)->getStorageSpecs(),
            'cpuSpecs' => app(CpuController::class)->getCpuSpecs(),
            'coolerSpecs' => app(CoolerController::class)->getCoolerSpecs(),
        ];
    }

    public function index() {
        $components = $this->getAllFormattedComponents();

        $perPage = 6;
        $currentPage = request()->get('page', 1);
        $currentPageItems = $components->forPage($currentPage, $perPage);

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $components->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('staff.componentdetails', array_merge(
            ['components' => $paginated],
            $this->getAllSpecs()
        ));
    }

    public function delete (string $type, string $id) {
        $modelMap = config('components'); // FOUND IN CONFIG FILE

        if (!array_key_exists($type, $modelMap)) {
            abort(404, "Unknown component type: {$type}");
        }   

        $model = $modelMap[$type];
        $component = $model::findOrFail($id);

        // DELETE PRODUCT IMAGE
        if ($component->image) {
            FacadesStorage::disk('public')->delete($component->image);
        }

        // DELETE 3D MODEL
        if ($component->model_3d) {
            FacadesStorage::disk('public')->delete($component->model_3d);
        }

        $component->delete();

        return back()->with([
            'message' => ucfirst($type) . ' has been deleted.',
            'type' => 'success',
        ]);
    }

    public function restore (string $type, string $id) {
        $modelMap = config('components'); // FOUND IN CONFIG FILE

        if (!array_key_exists($type, $modelMap)) {
            abort(404, "Unknown component type: {$type}");
        }   

        $model = $modelMap[$type];
        $component = $model::withTrashed()->findOrFail($id);
        $component->restore();

        return back()->with([
            'message' => ucfirst($type) . ' has been restored.',
            'type' => 'success',
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = strtolower($request->input('search'));

        $components = $this->getAllFormattedComponents()->filter(function ($component) use ($searchTerm) {
            return str_contains(strtolower($component['model']), $searchTerm)
                || str_contains(strtolower($component['brand']), $searchTerm);
        });

        // Pagination
        $perPage = 6;
        $currentPage = $request->get('page', 1);
        $currentPageItems = $components->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $components->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('staff.componentdetails', array_merge(
            ['components' => $paginated],
            $this->getAllSpecs()
        ));
    }
}
