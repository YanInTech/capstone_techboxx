<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Illuminate\Support\Facades\DB;
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
        $checkoutIdsRaw = $request->input('checkout_ids');
        $orderedBuildId = $request->input('ordered_build_id');
        $selected = $request->input('selected');
        $isDownpayment = $request->input('is_downpayment', 0);

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
                    'ordered_build_id' => $orderedBuildId,
                    'selected' => $selected,
                    'is_downpayment' => $isDownpayment
                ]),
                "cancel_url" => route('paypal.cancel', [
                    'checkout_ids' => $checkoutIdsRaw,
                    'ordered_build_id' => $orderedBuildId,
                    'selected' => $selected,
                    'is_downpayment' => $isDownpayment
                ]),
            ]
        ];

        try {
            $response = $this->client->execute($paypalRequest);
        } catch (\Exception $e) {
            Log::error('PayPal create error: ' . $e->getMessage());
            return redirect()->route('techboxx.build')->with('error', 'Unable to start PayPal payment.');
        }

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return redirect($link->href);
            }
        }

        return redirect()->route('techboxx.build')->with('error', 'Unable to start PayPal payment.');
    }

    public function success(Request $request)
    {
        $paypalOrderId = $request->query('token');
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $orderedBuildId = $request->query('ordered_build_id');
        $selectedRaw = $request->query('selected', '');
        $isDownpayment = $request->query('is_downpayment', 0);

        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        // $selectedIds = array_filter(array_map('intval', explode(',', $selectedRaw)));

        try {
            $captureRequest = new OrdersCaptureRequest($paypalOrderId);
            $captureResponse = $this->client->execute($captureRequest);
        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());
            return redirect()->route('techboxx.build')->with('error', 'Payment capture failed.');
        }

        if (isset($captureResponse->result->status) && $captureResponse->result->status === "COMPLETED") {
            // Update checkout payment statuses
            if (!empty($checkoutIds)) {
                $paymentStatus = $isDownpayment ? 'Downpayment Paid' : 'Paid';
                Checkout::whereIn('id', $checkoutIds)->update(['payment_status' => $paymentStatus]);
            }

            // Update ordered build payment status
            if ($orderedBuildId) {
                $paymentStatus = $isDownpayment ? 'Paid' : 'Paid';
                OrderedBuild::where('id', $orderedBuildId)->update(['payment_status' => $paymentStatus]);
            }

            $message = $isDownpayment ? '50% Downpayment successful! Order placed.' : 'Payment successful! Order placed.';
            return redirect()->route('cart.index')->with('success', $message);
        }

        return redirect()->route('techboxx.build')->with('error', 'Payment not completed.');
    }

    public function cancel(Request $request)
    {
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $orderedBuildId = $request->query('ordered_build_id');
        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        
        Log::info('PayPal cancellation triggered', [
            'checkout_ids' => $checkoutIds,
            'ordered_build_id' => $orderedBuildId,
            'all_params' => $request->query()
        ]);
        
        // Handle cart item cancellations (from CheckoutController)
        if (!empty($checkoutIds)) {
            Log::info('Processing checkout cancellations', ['checkout_ids' => $checkoutIds]);
            
            $checkouts = Checkout::whereIn('id', $checkoutIds)->get();
            
            foreach ($checkouts as $checkout) {
                $cartItem = $checkout->cartItem;
                
                if ($cartItem) {
                    $modelMap = config('components', []);
                    $productType = $cartItem->product_type;
                    $model = $modelMap[$productType] ?? null;
                    
                    if ($model) {
                        $model::where('id', $cartItem->product_id)
                            ->increment('stock', $cartItem->quantity);
                        
                        Log::info("Stock restored for {$productType} ID {$cartItem->product_id}: +{$cartItem->quantity} units");
                    }
                }
            }
            
            // Update payment status to Cancelled and delete
            Checkout::whereIn('id', $checkoutIds)->update([
                'payment_status' => 'Cancelled',
                'is_downpayment' => false,
                'downpayment_amount' => null,
                'remaining_balance' => null,
            ]);
            Checkout::whereIn('id', $checkoutIds)->delete();
            
            Log::info('Checkout records cancelled and deleted', ['count' => count($checkoutIds)]);
        }
        
        // Handle ordered build cancellation (from BuildController)
        if ($orderedBuildId) {
            Log::info('Processing ordered build cancellation', ['ordered_build_id' => $orderedBuildId]);
            
            $orderedBuild = OrderedBuild::find($orderedBuildId);
            
            if ($orderedBuild) {
                // Restore stock for all components in the build
                $this->restoreBuildStock($orderedBuild);
                
                // Update and soft delete the ordered build
                $orderedBuild->update([
                    'payment_status' => 'Cancelled',
                    'is_downpayment' => false,
                    'downpayment_amount' => null,
                    'remaining_balance' => null,
                ]);
                $orderedBuild->delete();
                
                Log::info("Ordered build ID {$orderedBuildId} cancelled and stock restored");
            } else {
                Log::warning("Ordered build not found for cancellation", ['ordered_build_id' => $orderedBuildId]);
            }
        }
        
        // Determine which route to redirect to based on what was cancelled
        if (!empty($checkoutIds)) {
            return redirect()->route('cart.index')->with('success', 'Payment was cancelled. Your cart items have been restored.');
        } elseif ($orderedBuildId) {
            return redirect()->route('techboxx.build')->with('success', 'Payment was cancelled. Your custom build has been cancelled.');
        }
        
        // Fallback redirect
        return redirect()->route('cart.index')->with('success', 'Payment was cancelled.');
    }

    private function restoreBuildStock(OrderedBuild $orderedBuild)
    {
        $userBuild = $orderedBuild->userBuild;
        
        if (!$userBuild) return;
        
        $components = [
            'pc_case_id' => 'pc_cases',
            'cooler_id' => 'coolers',
            'cpu_id' => 'cpus',
            'gpu_id' => 'gpus',
            'motherboard_id' => 'motherboards',
            'psu_id' => 'psus',
            'ram_id' => 'rams',
            'storage_id' => 'storages',
        ];
        
        foreach ($components as $componentField => $tableName) {
            if ($userBuild->$componentField) {
                DB::table($tableName)
                    ->where('id', $userBuild->$componentField)
                    ->increment('stock', 1); // Each build uses 1 of each component
                
                Log::info("Stock restored for {$tableName} ID {$userBuild->$componentField}: +1 unit");
            }
        }
    }
}