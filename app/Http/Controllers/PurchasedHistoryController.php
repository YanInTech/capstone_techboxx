<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PurchasedHistoryController extends Controller
{
    public function index()
    {
        // Load orders with items only (don't try to load items.component)
        $orders = Order::with('items')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // pass hardware map for optional lookup (if order_items ever store product_type)
        $hardwareMap = config('hardware', []);

        return view('customer.purchasedhistory', compact('orders', 'hardwareMap'));
    }

    // optional invoice view if you want it
    public function invoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items');
        $hardwareMap = config('hardware', []);
        return view('customer.invoice', compact('order', 'hardwareMap'));
    }
}