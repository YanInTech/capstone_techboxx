<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Payment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalController extends Controller
{
    private $client;

    public function __construct()
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    public function create(Request $request)
    {
        $amount = $request->input('amount');
        $order_id = $request->input('order_id');

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
                "return_url" => route('paypal.success', ['order_id' => $order_id]),
                "cancel_url" => route('paypal.cancel', ['order_id' => $order_id]),
            ]
        ];

        $response = $this->client->execute($paypalRequest);

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
        $orderId = $request->query('order_id');

        $captureRequest = new OrdersCaptureRequest($paypalOrderId);
        $captureResponse = $this->client->execute($captureRequest);

        if ($captureResponse->result->status === "COMPLETED") {
            $order = Order::find($orderId);
            $order->update(['status' => 'paid', 'payment_method' => 'PayPal']);

            $payer = $captureResponse->result->payer ?? null;
            $capture = $captureResponse->result->purchase_units[0]->payments->captures[0] ?? null;

            Payment::create([
                'order_id' => $order->id,
                'order_id_paypal' => $captureResponse->result->id ?? null,
                'payer_email' => $payer->email_address ?? null,
                'payer_name' => isset($payer->name) ? ($payer->name->given_name . ' ' . $payer->name->surname) : null,
                'status' => $captureResponse->result->status ?? null,
                'amount' => $capture->amount->value ?? null,
                'currency' => $capture->amount->currency_code ?? null,
                'transaction_id' => $capture->id ?? null,
            ]);

            return redirect()->route('cart.index')->with('success', 'Payment successful! Order placed.');
        }

        return redirect()->route('cart.index')->with('error', 'Payment not completed.');
    }

    public function cancel(Request $request)
    {
        $orderId = $request->query('order_id');
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'cancelled']);
            }
        }
        return redirect()->route('cart.index')->with('error', 'Payment was cancelled.');
    }
}