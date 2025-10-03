<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PurchasedHistoryController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $allCheckouts = Checkout::with([
            'cartItem.shoppingCart.user',
            'cartItem.case',
            'cartItem.cpu', 
            'cartItem.gpu',
            'cartItem.motherboard',
            'cartItem.ram',
            'cartItem.storage',
            'cartItem.psu',
            'cartItem.cooler'
        ])
        ->whereHas('cartItem.shoppingCart', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereNotNull('pickup_date')
        ->orderBy('checkout_date', 'desc')
        ->get();

        // Group by ShoppingCart ID + Checkout Timestamp
        $grouped = $allCheckouts->groupBy(function ($checkout) {
            return $checkout->cartItem->shopping_cart_id . '|' . $checkout->checkout_date->format('Y-m-d H:i:s');
        });

        // Transform each group
        $groupedCheckouts = $grouped->map(function ($checkouts) {
            $first = $checkouts->first();
            $cartItems = $checkouts->map->cartItem;

            return [
                'shopping_cart_id' => $first->cartItem->shopping_cart_id,
                'checkout_date' => $first->checkout_date,
                'pickup_date' => $first->pickup_date,
                'updated_at' => $first->updated_at,
                'pickup_status' => $first->pickup_status,
                'total_cost' => $checkouts->sum('total_cost'),
                'cart_items' => $cartItems->values(),
                'payment_method' => $first->payment_method,
                'user' => $first->cartItem->shoppingCart->user,
            ];
        })->values();

        // Manual pagination
        $page = request()->get('page', 1);
        $perPage = 1;
        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedCheckouts->forPage($page, $perPage),
            $groupedCheckouts->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('customer.purchasedhistory', compact('paginatedGroups'));
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