<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CheckoutDetailsController extends Controller
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
                'total_cost' => $checkouts->sum('total_cost'),
                'cart_items' => $cartItems->values(),
                'payment_method' => $first->payment_method,
                'user' => $first->cartItem->shoppingCart->user,
            ];
        })->values();

        return view('customer.checkoutdetails', compact('groupedCheckouts'));


        // $userId = Auth::id();

        // // Get latest order for the logged-in user
        // $order = Order::where('user_id', $userId)
        //     ->latest()
        //     ->with(['items', 'user'])
        //     ->first();

        // if (!$order) {
        //     return view('customer.checkoutdetails', [
        //         'checkoutItems' => collect(),
        //         'total' => 0,
        //         'order' => null,
        //         'contactNumber' => 'N/A',
        //     ]);
        // }

        // // ✅ Use data directly from order_items table (like cart does)
        // $checkoutItems = $order->items->map(function ($item) {
        //     return [
        //         'component' => $item->name ?? 'Unknown',
        //         'category'  => $item->category ?? ucfirst($item->product_type ?? 'N/A'),
        //         'qty'       => $item->quantity ?? 1,
        //         'price'     => $item->price ?? 0,
        //     ];
        // });

        // $total = $checkoutItems->sum(fn($it) => $it['price'] * $it['qty']);

        // // Resolve contact number
        // $contactNumber = $order->contact
        //     ?? $order->phone
        //     ?? $order->phone_number
        //     ?? ($order->user->contact ?? $order->user->phone ?? $order->user->phone_number ?? null)
        //     ?? 'N/A';

        // return view('customer.checkoutdetails', [
        //     'checkoutItems' => $checkoutItems,
        //     'total' => $total,
        //     'order' => $order,
        //     'contactNumber' => $contactNumber,
        // ]);
    }
}