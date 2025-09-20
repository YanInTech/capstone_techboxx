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

        if (strtolower($paymentMethod) === 'cash on pickup') {
            $paymentMethod = 'Cash';
        }

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

            // Iterate through selected cart items and create Checkout records
            foreach ($cartItems as $ci) {
                $product = $ci->product;
                $name = $product->brand ?? ($product->name ?? ($product->model ?? 'Product'));

                // Prepare the data before creating the Checkout record
                $data = [
                    'cart_item_id' => $ci->id,
                    'checkout_date' => now()->toDateTimeString(),
                    'total_cost' => $ci->total_price,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentMethod === 'Cash' ? 'Pending' : 'Paid',
                ];

                // Create the Checkout record for each cart item
                Checkout::create($data);

                $ci->update(['processed' => true]);
            }

            // Redirect to PayPal payment page if PayPal is selected
            if ($paymentMethod === 'PayPal') {
                return redirect()->route('paypal.create', [
                    'amount' => $grandTotal,
                ]);
            }

            // If payment is Cash on Pickup, no need to delete the cart items
            // Just show the success message
            return redirect()->route('cart.index')->with('success', 'Order placed successfully!');

        }

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
    }
}
