<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderDetailsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get orders for this user
        $orders = Order::where('user_id', $user->id)->get();

        return view('customer.orderdetails', compact('orders'));
    }
}