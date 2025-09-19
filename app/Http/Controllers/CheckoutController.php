<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $user = Auth::user();
        $selectedItemsRaw = $request->input('selected_items', '[]');
        $selectedIds = json_decode($selectedItemsRaw, true) ?: [];
        $paymentMethod = $request->input('payment_method', 'Cash on Pickup');

        if ($user) {
            $shoppingCart = $user->shoppingCart;
            if (!$shoppingCart) {
                return redirect()->back()->with('error', 'Your cart is empty!');
            }

            $cartItems = $shoppingCart->cartItem()->whereIn('id', $selectedIds)->get();
            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'No items selected.');
            }

            $modelMap = config('components', []);
            foreach ($cartItems as $ci) {
                $model = $modelMap[$ci->product_type] ?? null;
                $ci->product = $model ? $model::find($ci->product_id) : null;
            }

            $grandTotal = $cartItems->sum(fn($i) => ($i->product->price ?? 0) * ($i->quantity ?? 0));

            // $order = Order::create([
            //     'user_id' => $user->id,
            //     'payment_method' => $paymentMethod,
            //     'total' => $grandTotal,
            //     'status' => 'pending',
            // ]);

            foreach ($cartItems as $ci) {
                $product = $ci->product;
                $name = $product->brand ?? ($product->name ?? ($product->model ?? 'Product'));
                Checkout::create([
                    'cart_item_id' => $ci->id,
                    'product_id' => $ci->product_id,
                    'name' => $name,
                    'quantity' => $ci->quantity,
                    'price' => $product->price ?? 0,
                    'subtotal' => ($product->price ?? 0) * $ci->quantity,
                ]);
            }

            // if ($paymentMethod === 'PayPal') {
            //     return redirect()->route('paypal.create', [
            //         'order_id' => $order->id,
            //         'amount' => $grandTotal,
            //     ]);
            // }

            foreach ($cartItems as $ci) {
                $ci->delete();
            }

            return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
        }

        $sessionCart = session()->get('cart', []);
        if (empty($sessionCart)) {
            return redirect()->back()->with('error', 'Your cart is empty!');
        }

        $items = [];
        foreach ($selectedIds as $k) {
            if (isset($sessionCart[$k])) {
                $items[$k] = $sessionCart[$k];
            }
        }

        $grandTotal = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);

        $order = Order::create([
            'user_id' => null,
            'payment_method' => $paymentMethod,
            'total' => $grandTotal,
            'status' => 'pending',
        ]);

        foreach ($items as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        if ($paymentMethod === 'PayPal') {
            return redirect()->route('paypal.create', [
                'order_id' => $order->id,
                'amount' => $grandTotal,
            ]);
        }

        foreach ($selectedIds as $id) {
            unset($sessionCart[$id]);
        }
        session()->put('cart', $sessionCart);

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
    }
}