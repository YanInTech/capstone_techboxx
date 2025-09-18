<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = OrderedBuild::with([
            'userBuild.case',
            'userBuild.cpu',
            'userBuild.motherboard',
            'userBuild.gpu',
            'userBuild.ram',
            'userBuild.storage',
            'userBuild.psu',
            'userBuild.cooler', 
            'userBuild.user',
            ])
            ->where(function ($query) {
                $query->where('status', 'Pending')
                    ->orWhere('user_id', Auth::id());
            })
            ->orderByRaw("
                CASE 
                    WHEN status = 'Approved' AND pickup_date IS NULL THEN 1
                    WHEN status = 'Pending' AND pickup_date IS NULL THEN 2
                    WHEN status = 'Picked Up' AND pickup_date IS NULL THEN 3
                    WHEN status = 'Picked Up' AND pickup_date IS NOT NULL THEN 4
                    WHEN status = 'Approved' AND pickup_date IS NOT NULL THEN 5
                    WHEN status = 'Declined' THEN 6
                    ELSE 7
                END
            ")
            ->orderBy('created_at', 'asc')  // FIFO within groups (oldest first)
            ->paginate(5);

        $checkouts = Checkout::
            orderByRaw("
                CASE 
                    WHEN pickup_status = 'Pending' AND pickup_date IS NULL THEN 1
                    WHEN pickup_status IS NULL AND pickup_date IS NULL THEN 2
                    WHEN pickup_status = 'Picked up' AND pickup_date IS NOT NULL THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'asc')  // FIFO within groups (oldest first)
            ->paginate(5);

        return view('staff.order', compact('orders', 'checkouts'));
    }


    public function approve($id) {
        $order = OrderedBuild::findOrFail($id);

        $order->update([
            'status' => 'Approved',
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order approved',
            'type' => 'success',
        ]);
    }

    public function decline($id) {
        $order = OrderedBuild::findOrFail($id);

        $order->update([
            'status' => 'Declined',
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order declined',
            'type' => 'success',
        ]);
    }

    public function ready($id) {
        $order = OrderedBuild::findOrFail($id);

        $order->update([
            'pickup_status' => 'Pending',
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order ready for pickup',
            'type' => 'success',
        ]);
    }

    public function readyComponents($id) {
        $order = Checkout::findOrFail($id);

        $order->update([
            'pickup_status' => 'Pending',
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order ready for pickup',
            'type' => 'success',
        ]);
    }

    public function pickup($id) {
        $order = OrderedBuild::findOrFail($id);

        $order->update([
            'pickup_status' => 'Picked up',
            'pickup_date' =>now(),
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order marked as picked up',
            'type' => 'success',
        ]);
    }

    public function pickupComponents($id) {
        $order = Checkout::findOrFail($id);

        $order->update([
            'pickup_status' => 'Picked up',
            'pickup_date' =>now(),
        ]);

        return redirect()->route('staff.order')->with([
            'message' => 'Order marked as picked up',
            'type' => 'success',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
