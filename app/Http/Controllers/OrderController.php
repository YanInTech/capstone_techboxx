<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\ShoppingCart;
use App\Models\UserBuild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $shoppingCarts = ShoppingCart::select('shopping_carts.*', 'checkouts.pickup_status', 'checkouts.pickup_date')
            ->distinct()
            ->join('cart_items', 'cart_items.shopping_cart_id', '=', 'shopping_carts.id')
            ->join('checkouts', 'checkouts.cart_item_id', '=', 'cart_items.id')
            ->with([
                'user',
                'cartItem' => function ($query) {
                    $query->whereHas('checkout'); // ✅ only include cartItem that have checkout
                },
                'cartItem.checkout',
                'cartItem.case',
                'cartItem.cpu',
                'cartItem.motherboard',
                'cartItem.gpu',
                'cartItem.ram',
                'cartItem.storage',
                'cartItem.psu',
                'cartItem.cooler',
            ])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('checkouts')
                    ->whereColumn('checkouts.cart_item_id', 'cart_items.id');
            })
            ->orderByRaw("
                CASE 
                    WHEN checkouts.pickup_status = 'Pending' AND checkouts.pickup_date IS NULL THEN 1
                    WHEN checkouts.pickup_status IS NULL AND checkouts.pickup_date IS NULL THEN 2
                    WHEN checkouts.pickup_status = 'Picked up' AND checkouts.pickup_date IS NOT NULL THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('shopping_carts.created_at', 'asc')
            ->paginate(5);


        return view('staff.order', compact('orders', 'shoppingCarts'));
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

    // public function readyComponents($id) {
    //     $order = Checkout::findOrFail($id);

    //     $order->update([
    //         'pickup_status' => 'Pending',
    //     ]);

    //     return redirect()->route('staff.order')->with([
    //         'message' => 'Order ready for pickup',
    //         'type' => 'success',
    //     ]);
    // }
    
    public function readyComponents($cartId)
    {
        // Find the shopping cart by ID
        $cart = ShoppingCart::with('cartItem.checkout')->findOrFail($cartId);

        // Loop through each cart item and update its related checkout
        foreach ($cart->cartItem as $cartItem) {
            foreach ($cartItem->checkout as $checkouts) {
                $checkouts->update([
                    'pickup_status' => 'Pending',
                ]);
            }
        }

        return redirect()->route('staff.order')->with([
            'message' => 'All related cart items are now ready for pickup',
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

    public function pickupComponents($cartId)
    {
        // Find the shopping cart by ID and load related cart items and their checkouts
        $cart = ShoppingCart::with('cartItem.checkout')->findOrFail($cartId);

        // Loop through each cart item and update its related checkout to "Picked up"
        foreach ($cart->cartItem as $cartItem) {
            foreach ($cartItem->checkout as $checkouts) {
                $checkouts->update([
                    'pickup_status' => 'Picked up',
                    'pickup_date' => now(),
                ]);
            }
        }

        return redirect()->route('staff.order')->with([
            'message' => 'All related cart items have been marked as picked up',
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
