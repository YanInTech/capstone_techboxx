<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\OrderedBuild;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\UserBuild;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function orderBuild(Request $request) 
    {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please log in to place an order.']);
            }
            return redirect()->route('login')->with('error', 'Please log in to place an order.');
        }
        
        // Validate the main build data
        $validated = $request->validate([
            'build_name' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:PayPal,PayPal_Downpayment,Cash on Pickup',
            'downpayment_amount' => 'nullable|numeric|min:0',
        ]);

        // Validate component IDs from the component_ids array
        $componentIds = $request->input('component_ids', []);
        
        // Define required components and their tables
        $requiredComponents = [
            'case' => 'pc_cases',
            'cooler' => 'coolers', 
            'cpu' => 'cpus',
            'gpu' => 'gpus',
            'motherboard' => 'motherboards',
            'psu' => 'psus',
            'ram' => 'rams',
            'storage' => 'storages',
        ];

        // Validate each required component - FIXED: Handle both AJAX and regular requests
        foreach ($requiredComponents as $componentType => $tableName) {
            if (!isset($componentIds[$componentType]) || empty($componentIds[$componentType])) {
                $error = "Missing $componentType component.";
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error]);
                }
                return redirect()->back()->with('error', $error);
            }
            
            // Check if the component exists in the database
            if (!DB::table($tableName)->where('id', $componentIds[$componentType])->exists()) {
                $error = "Invalid $componentType selected.";
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error]);
                }
                return redirect()->back()->with('error', $error);
            }
        }

        // Determine payment status and method
        $paymentMethod = $request->input('payment_method');
        $isDownpayment = $paymentMethod === 'PayPal_Downpayment';
        
        // Set payment status - for downpayment, it should be 'Downpayment Paid' after PayPal success
        $paymentStatus = $paymentMethod === 'Cash on Pickup' ? 'Pending' : ($isDownpayment ? 'Paid' : 'Paid');
        
        $displayPaymentMethod = $paymentMethod === 'Cash on Pickup' ? 'Cash' : 'PayPal';

        // Calculate downpayment amount if not provided
        $downpaymentAmount = $isDownpayment ? 
            ($request->input('downpayment_amount') ?? $validated['total_price'] * 0.5) : 
            null;

        try {
            // Create UserBuild record
            $userBuild = UserBuild::create([
                'user_id' => $user->id,
                'build_name' => $validated['build_name'],
                'pc_case_id' => $componentIds['case'],
                'cooler_id' => $componentIds['cooler'],
                'cpu_id' => $componentIds['cpu'],
                'gpu_id' => $componentIds['gpu'],
                'motherboard_id' => $componentIds['motherboard'],
                'psu_id' => $componentIds['psu'],
                'ram_id' => $componentIds['ram'],
                'storage_id' => $componentIds['storage'],
                'total_price' => $validated['total_price'],
                'status' => 'Ordered',
            ]);

            // Create OrderedBuild record
            $orderedBuild = OrderedBuild::create([
                'user_build_id' => $userBuild->id,
                'payment_method' => $displayPaymentMethod,
                'payment_status' => $paymentStatus,
                'status' => 'Pending',
                'is_downpayment' => $isDownpayment,
                'downpayment_amount' => $downpaymentAmount,
                'remaining_balance' => $isDownpayment ? ($validated['total_price'] - $downpaymentAmount) : null,
            ]);

            // Handle PayPal redirection
            if ($paymentMethod === 'PayPal' || $paymentMethod === 'PayPal_Downpayment') {
                $amount = $isDownpayment ? $downpaymentAmount : $validated['total_price'];
                
                // For AJAX requests, return JSON with redirect URL
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('paypal.create', [
                            'ordered_build_id' => $orderedBuild->id,
                            'amount' => $amount,
                            'is_downpayment' => $isDownpayment ? 1 : 0,
                            'checkout_ids' => '' // Explicitly set empty for ordered builds
                        ])
                    ]);
                }
                
                // For regular form submissions, redirect directly
                return redirect()->route('paypal.create', [
                    'ordered_build_id' => $orderedBuild->id,
                    'amount' => $amount,
                    'is_downpayment' => $isDownpayment ? 1 : 0,
                    'checkout_ids' => '' // Explicitly set empty for ordered builds
                ]);
            }

            // For non-PayPal payments and AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('customer.orderdetails')
                ]);
            }

            return redirect()->route('customer.orderdetails')->with([
                'message' => 'Build ordered successfully!',
                'type' => 'success',
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Order build failed: ' . $e->getMessage());
            // \Log::error('Exception trace: ' . $e->getTraceAsString());
            
            $error = 'Failed to create order. Please try again.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error]);
            }
            return redirect()->back()->with('error', $error);
        }
    }

    public function orderSavedBuild(Request $request)
    {
        // Determine payment status and method
        $paymentMethod = $request->input('payment_method');
        $isDownpayment = $paymentMethod === 'PayPal_Downpayment';
        
        // Set payment status - for downpayment, it should be 'Downpayment Paid' after PayPal success
        $paymentStatus = $isDownpayment ? 'Downpayment Paid' : 'Paid';
        
        $displayPaymentMethod = 'PayPal'; // Always PayPal since we removed Cash on Pickup

        // Calculate downpayment amount if applicable
        $downpaymentAmount = $isDownpayment ? ($request->input('downpayment_amount') ?? $request->total_price * 0.5) : null;
        $remainingBalance = $isDownpayment ? ($request->total_price - $downpaymentAmount) : null;

        // Create OrderedBuild record
        $orderedBuild = OrderedBuild::create([
            'user_build_id' => $request->user_build_id,
            'status' => "Pending",
            'payment_method' => $displayPaymentMethod,
            'payment_status' => $paymentStatus,
            'is_downpayment' => $isDownpayment,
            'downpayment_amount' => $downpaymentAmount,
            'remaining_balance' => $remainingBalance,
        ]);

        UserBuild::where('id', $request->user_build_id)->update([
            'status' => "Ordered",
        ]);

        // Redirect based on payment method
        if ($paymentMethod === 'PayPal' || $paymentMethod === 'PayPal_Downpayment') {
            $amount = $isDownpayment ? $downpaymentAmount : $request->total_price;
            
            return redirect()->route('paypal.create', [
                'ordered_build_id' => $orderedBuild->id,
                'amount' => $amount,
                'is_downpayment' => $isDownpayment ? 1 : 0,
                'checkout_ids' => '' // Explicitly set empty for saved builds
            ]);
        }

        return redirect()->route('customer.orderdetails')->with([
            'message' => 'Build ordered successfully!',
            'type' => 'success',
        ]);
    }

    // MBA Bundle Add to Cart
    public function addBundle(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $user = Auth::user();
            $cart = $user->shoppingCart;
            
            $checkedItemsJson = $request->input('checked_items');
            $checkedItems = json_decode($checkedItemsJson, true) ?? [];
            
            $addedItems = [];

            // Add all checked items to cart
            foreach ($checkedItems as $item) {
                if (is_array($item) && isset($item['id']) && isset($item['type']) && isset($item['table'])) {
                    $addedItems[] = $this->addItemToCart($cart, $item['id'], $item['type'], $item['table'], $item['price'] ?? null);
                }
            }

            // Create success message with all added items
            $itemNames = array_filter($addedItems);
            if (!empty($itemNames)) {
                $message = count($itemNames) . ' items added to cart: ' . implode(', ', $itemNames);
            } else {
                $message = 'No items were added to cart.';
            }

            return redirect()->back()->with([
                'message' => $message,
                'type' => 'success',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Failed to add items to cart: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    // Helper function to add individual items to cart (keep this the same)
    private function addItemToCart($cart, $productId, $productType, $productTable, $price = null)
    {
        // If price is not provided, fetch it from the database
        if ($price === null) {
            $product = DB::table($productTable)->find($productId);
            $price = $product->price ?? 0;
        }

        // If cart doesn't exist, create one
        if (!$cart) {
            $user = Auth::user();
            $cart = ShoppingCart::create(['user_id' => $user->id]);
        }

        // Check if the item already exists in cart
        $cartItem = $cart->cartItem()->where('product_id', $productId)
                                    ->where('product_type', $productType)
                                    ->where('processed', false)
                                    ->first();

        if ($cartItem) {
            // If item already exists, increment quantity and update total price
            $cartItem->increment('quantity');
            $newTotalPrice = $cartItem->total_price * $cartItem->quantity;
            $cartItem->update(['total_price' => $newTotalPrice]);
            
            // Get product name for success message
            $product = DB::table($productTable)->find($productId);
            return $product->brand . ' ' . $product->model ?? 'Unknown Product';
        } else {
            // If item doesn't exist, create new cart item
            $cart->cartItem()->create([
                'product_id' => $productId,
                'product_type' => $productType,
                'quantity' => 1,
                'total_price' => $price,
                'processed' => false,
            ]);
            
            // Get product name for success message
            $product = DB::table($productTable)->find($productId);
            return $product->brand . ' ' . $product->model ?? 'Unknown Product';
        }
    }
}
