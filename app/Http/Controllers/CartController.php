<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Show cart page
    public function index()
    {
        $user = Auth::user();

        // Check if user has a shopping cart
        $shoppingCart = $user ? $user->shoppingCart : null;

        // If the user has a shopping cart, fetch the cart items
        $cart = $shoppingCart ? $shoppingCart->cartItem()->where('processed', false)->get() : [];

        // Fetch product data dynamically based on product type
        foreach ($cart as $item) {
            $modelMap = config('components'); // Assuming your config file contains a map of product types to models
            $model = $modelMap[$item->product_type] ?? null; // Get the model based on the product_type
            
            // Check if model is valid
            if ($model) {
                // Fetch the product using the model and product_id from the cart item
                $item->product = $model::find($item->product_id); // This assumes each model has a `find` method
            }
        }

        // Return the view with the cart items
        return view('cart', compact('cart'));
    }
    
    // Add product to cart
    public function add(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $cart = $user->shoppingCart;

        if ($cart) {
            // Cart exists — check if the item with both product_id and product_type is already in the cart
            $cartItem = $cart->cartItem()->where('product_id', $request->input('product_id'))
                                        ->where('product_type', $request->input('component_type'))
                                        ->where('processed',false)
                                        ->first();

            if ($cartItem) {
                // If item already exists, increment quantity and update the total price
                $cartItem->increment('quantity');
                $newTotalPrice = $cartItem->total_price * $cartItem->quantity;
                $cartItem->update(['total_price' => $newTotalPrice]);
            } else {
                // If item does not exist, create a new cart item
                $cart->cartItem()->create([
                    'product_id' => $request->input('product_id'),
                    'product_type' => $request->input('component_type'),
                    'quantity' => 1,
                    'total_price' => $request->input('price'),
                    'processed' => false,
                ]);
            }
        } else {
            // Cart doesn't exist — create one first
            $cart = ShoppingCart::create(['user_id' => $user->id]);

            $cart->cartItem()->create([
                'product_id' => $request->input('product_id'),
                'product_type' => $request->input('component_type'),
                'quantity' => 1,
                'total_price' => $request->input('price'),
                'processed' => false,
            ]);
        }


        return back()->with([
            'message' => $request->input('name') . ' added to cart!',
            'type' => 'success',
        ]);
    }

    // Update quantity
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Check if user has a shopping cart
        $shoppingCart = $user ? $user->shoppingCart : null;

        if ($shoppingCart) {
            // Find the cart item by its ID
            $cartItem = $shoppingCart->cartItem()->find($id);

            if ($cartItem) {
                // Update quantity based on the action
                if ($request->action === 'increase') {
                    $cartItem->increment('quantity');
                } elseif ($request->action === 'decrease' && $cartItem->quantity > 1) {
                    $cartItem->decrement('quantity');
                }

                // Fetch the product model based on the product type
                $modelMap = config('components'); // Assuming your config file contains a map of product types to models
                $model = $modelMap[$cartItem->product_type] ?? null; // Get the model based on the product_type

                // Check if model is valid
                if ($model) {
                    // Fetch the product using the model and product_id from the cart item
                    $product = $model::find($cartItem->product_id);

                    if ($product) {
                        // Update the total price by multiplying quantity by the product price
                        $cartItem->total_price = $cartItem->quantity * $product->price;
                        $cartItem->save();
                    } else {
                        // Handle case if the product is not found
                        return redirect()->back()->with('error', 'Product not found.');
                    }
                } else {
                    // Handle case if model is invalid
                    return redirect()->back()->with('error', 'Invalid product type.');
                }
            }
        }

        return redirect()->back()->with('success', 'Cart updated successfully!');
    }


    // Remove product
    public function remove($id)
    {
        $user = Auth::user();

        // Check if user has a shopping cart
        $shoppingCart = $user ? $user->shoppingCart : null;

        if ($shoppingCart) {
            // Find the cart item by its ID
            $cartItem = $shoppingCart->cartItem()->find($id);

            if ($cartItem) {
                // Remove the cart item from the shopping cart
                $cartItem->delete();
            }
        }

        return redirect()->back()->with('success', 'Product removed from cart!');
    }

    // Checkout selected items
    public function checkout(Request $request)
    {
        $user = Auth::user();
        
        // Retrieve selected item IDs from the cart page (these are passed as a hidden input)
        $selectedItemIds = json_decode($request->get('selected_items'), true);
        
        if (empty($selectedItemIds)) {
            return redirect()->route('cart.index')->with('error', 'No items selected for checkout.');
        }
        
        // Fetch user's shopping cart and the items from it
        $shoppingCart = $user ? $user->shoppingCart : null;
        $cartItems = $shoppingCart ? $shoppingCart->cartItem : [];
        
        // Convert the Eloquent collection to a plain array and filter the selected items
        $selectedItems = collect($cartItems)->filter(function ($item) use ($selectedItemIds) {
            return in_array($item->id, $selectedItemIds);
        });

        // Fetch product data dynamically based on the product type for each selected item
        foreach ($selectedItems as $item) {
            $modelMap = config('components');
            $model = $modelMap[$item->product_type] ?? null;
            
            if ($model) {
                $item->product = $model::find($item->product_id); // Get product details from the model
            }
        }
        
        // Return the checkout view with the selected items
        return view('checkout', compact('selectedItems'));
    }


}
