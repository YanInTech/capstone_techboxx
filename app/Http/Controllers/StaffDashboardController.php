<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StockHistory;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index()
    {
        // 1. Orders in progress
        $ordersInProgress = Order::where('status', 'in-progress')->count();

        // 2. Low stock / inventory warnings
        $lowStockThreshold = 5;
        $inventoryWarnings = app(ComponentDetailsController::class)
            ->getAllFormattedComponents()
            ->filter(fn($component) => $component->stock <= $lowStockThreshold)
            ->count();

        // 3. Tasks (pending orders for approval)
        $tasks = Order::where('status', 'pending')->get();

        // 4. Notifications
        $notifications = [
            // Stock movements
            'stockIns'  => StockHistory::where('action', 'stock-in')->latest()->take(5)->get(),
            'stockOuts' => StockHistory::where('action', 'stock-out')->latest()->take(5)->get(),

            // Reorder alerts
            'reorders'  => app(ComponentDetailsController::class)
                ->getAllFormattedComponents()
                ->filter(fn($component) => $component->stock <= 0),
        ];

        return view('staff.dashboard', compact(
            'ordersInProgress',
            'inventoryWarnings',
            'tasks',
            'notifications'
        ));
    }
}