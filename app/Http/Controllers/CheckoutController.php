<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $user = Auth::user();
        $selectedItemsRaw = $request->input('selected_items', '[]');
        $selectedIds = json_decode($selectedItemsRaw, true) ?: [];
        $paymentMethod = $request->input('payment_method', 'Cash on Pickup');
        $downpaymentAmount = $request->input('downpayment_amount');

        // Determine if this is a downpayment
        $isDownpayment = $paymentMethod === 'PayPal_Downpayment';
        
        if ($isDownpayment) {
            $paymentMethod = 'PayPal'; // Store as PayPal but track downpayment
        } elseif (strtolower($paymentMethod) === 'cash on pickup') {
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
            
            // Validate stock availability first
            $stockErrors = [];
            foreach ($cartItems as $ci) {
                $model = $modelMap[$ci->product_type] ?? null;
                $product = $model ? $model::find($ci->product_id) : null;
                
                if ($product) {
                    $ci->setRelation('product', $product);
                    
                    // Check if sufficient stock is available
                    if ($product->stock < $ci->quantity) {
                        $stockErrors[] = "Insufficient stock for {$product->brand} {$product->model}. Available: {$product->stock}, Requested: {$ci->quantity}";
                    }
                }
            }
            
            if (!empty($stockErrors)) {
                return redirect()->route('cart.index')->with('error', implode('<br>', $stockErrors));
            }

            $grandTotal = $cartItems->sum(fn($i) => ($i->product->price ?? 0) * ($i->quantity ?? 0));
            
            // Calculate the amount to charge
            $amountToCharge = $isDownpayment ? $downpaymentAmount : $grandTotal;

            DB::beginTransaction();
            
            try {
                $checkoutIds = [];
                
                foreach ($cartItems as $ci) {
                    $product = $ci->product;
                    $name = $product->brand ?? ($product->name ?? ($product->model ?? 'Product'));

                    // Decrement the stock
                    $model = $modelMap[$ci->product_type] ?? null;
                    if ($model) {
                        $affected = $model::where('id', $ci->product_id)
                                         ->where('stock', '>=', $ci->quantity)
                                         ->decrement('stock', $ci->quantity);
                        
                        if (!$affected) {
                            throw new \Exception("Failed to update stock for {$name}. Possibly insufficient stock.");
                        }
                    }

                    // Prepare checkout data
                    $data = [
                        'cart_item_id' => $ci->id,
                        'checkout_date' => now()->toDateTimeString(),
                        'total_cost' => $ci->total_price,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $isDownpayment ? 'Paid' : 'Pending',
                        'is_downpayment' => $isDownpayment,
                        'downpayment_amount' => $isDownpayment ? $downpaymentAmount : null,
                        'remaining_balance' => $isDownpayment ? ($grandTotal - $downpaymentAmount) : null,
                    ];

                    $checkout = Checkout::create($data);
                    $checkoutIds[] = $checkout->id;

                    $ci->update(['processed' => true]);
                }

                DB::commit();

                // Redirect to PayPal if PayPal or Downpayment is selected
                if ($paymentMethod === 'PayPal') {
                    return redirect()->route('paypal.create', [
                        'checkout_ids' => implode(',', $checkoutIds),
                        'amount' => $amountToCharge,
                        'selected' => implode(',', $selectedIds),
                        'is_downpayment' => $isDownpayment ? 1 : 0,
                    ]);
                }

                return redirect()->route('cart.index')->with('success', 'Order placed successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Checkout process failed: ' . $e->getMessage());
                return redirect()->route('cart.index')->with('error', 'Checkout failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
    }
}