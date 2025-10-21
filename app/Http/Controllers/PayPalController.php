<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Checkout;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalController extends Controller
{
    private $client;

    public function __construct()
    {
        $clientId = env('PAYPAL_CLIENT_ID') ?: env('CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET') ?: env('CLIENT_SECRET');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    public function create(Request $request)
    {
        $amount = $request->input('amount');
        $checkoutIdsRaw = $request->input('checkout_ids'); // Comma-separated checkout IDs
        $selected = $request->input('selected'); // Comma-separated cart item IDs

        $paypalRequest = new OrdersCreateRequest();
        $paypalRequest->prefer('return=representation');
        $paypalRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "PHP",
                    "value" => (string) $amount
                ]
            ]],
            "application_context" => [
                "return_url" => route('paypal.success', [
                    'checkout_ids' => $checkoutIdsRaw,
                    'selected' => $selected
                ]),
                "cancel_url" => route('paypal.cancel', [
                    'checkout_ids' => $checkoutIdsRaw,
                    'selected' => $selected
                ]),
            ]
        ];

        try {
            $response = $this->client->execute($paypalRequest);
        } catch (\Exception $e) {
            Log::error('PayPal create error: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Unable to start PayPal payment.');
        }

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return redirect($link->href);
            }
        }

        return redirect()->route('cart.index')->with('error', 'Unable to start PayPal payment.');
    }

    public function success(Request $request)
    {
        $paypalOrderId = $request->query('token');
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $selectedRaw = $request->query('selected', '');

        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        $selectedIds = array_filter(array_map('intval', explode(',', $selectedRaw)));

        try {
            $captureRequest = new OrdersCaptureRequest($paypalOrderId);
            $captureResponse = $this->client->execute($captureRequest);
        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Payment capture failed.');
        }

        if (isset($captureResponse->result->status) && $captureResponse->result->status === "COMPLETED") {
            // Update ALL checkout payment statuses
            if (!empty($checkoutIds)) {
                Checkout::whereIn('id', $checkoutIds)->update(['payment_status' => 'Paid']);
            }

            // Clear selected cart items
            if (Auth::check()) {
                $shoppingCart = Auth::user()->shoppingCart;
                if ($shoppingCart && !empty($selectedIds)) {
                    $shoppingCart->cartItem()->whereIn('id', $selectedIds)->delete();
                }
            }

            return redirect()->route('cart.index')->with('success', 'Payment successful! Order placed.');
        }

        return redirect()->route('cart.index')->with('error', 'Payment not completed.');
    }

    public function cancel(Request $request)
    {
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        
        // Delete ALL checkout records if payment was cancelled
        if (!empty($checkoutIds)) {
            // Get all checkout records before updating/deleting
            $checkouts = Checkout::whereIn('id', $checkoutIds)->get();
            
            // Restore stock for each cancelled item
            foreach ($checkouts as $checkout) {
                // Get the cart item associated with this checkout
                $cartItem = $checkout->cartItem;
                
                if ($cartItem) {
                    $modelMap = config('components', []);
                    $productType = $cartItem->product_type;
                    $model = $modelMap[$productType] ?? null;
                    
                    if ($model) {
                        // Increment the stock by the quantity that was ordered
                        $model::where('id', $cartItem->product_id)
                            ->increment('stock', $cartItem->quantity);
                        
                        // Optional: Log the stock restoration
                        Log::info("Stock restored for {$productType} ID {$cartItem->product_id}: +{$cartItem->quantity} units");
                    }
                }
            }
            
            Checkout::whereIn('id', $checkoutIds)->update(['payment_status' => 'Cancelled']);
            Checkout::whereIn('id', $checkoutIds)->delete();
        }
        
        return redirect()->route('cart.index')->with('success', 'Payment was cancelled.');
    }
}