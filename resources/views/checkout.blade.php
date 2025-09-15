<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Page Header -->
    <div class="w-full shadow-lg">
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-extrabold">Maddox.qwe | Checkout</h1>
            </div>
        </div>
    </div>

    <div class="w-full">
        <div class="rounded-xl p-6 w-full">

            <!-- Back to cart -->
            <div class="mb-4">
                <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-blue-900 font-medium">
                    ← Back to Cart
                </a>
            </div>

            <!-- Checkout Table -->
            <div class="overflow-x-auto border border-gray-200 rounded-xl mb-2">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-gray-700 text-xs md:text-sm uppercase tracking-wide border-b border-100">
                            <th class="p-4 text-left w-[30%]">Products Ordered</th>
                            <th class="p-4 text-left">Category</th>
                            <th class="p-4 text-center">Price</th>
                            <th class="p-4 text-center">Quantity</th>
                            <th class="p-4 text-center">Item Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($selectedItems as $item)
                            @php
                                $itemTotal = $item->product ? $item->product->price * $item->quantity : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <!-- Product -->
                                <td class="p-4">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $item->product->image ?? 'https://via.placeholder.com/72' }}" 
                                            alt="{{ $item->product->brand }} {{ $item->product->model }}" 
                                            class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ $item->product->brand }} {{ $item->product->model }}</div>
                                            @if($item->product->sku)
                                                <div class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-600">{{ ucfirst($item->product_type) ?? '—' }}</td>
                                <td class="p-4 text-center text-gray-800">₱{{ number_format($item->product->price, 2) }}</td>
                                <td class="p-4 text-center">
                                    <span class="px-4 font-semibold text-gray-900 select-none">{{ $item->quantity }}</span>
                                </td>
                                <td class="p-4 text-center font-semibold text-gray-900">₱{{ number_format($itemTotal, 2) }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <!-- Order Total (below table, aligned right) -->
            <div class="flex justify-end items-center text-sm font-semibold text-gray-900 mb-6">
                @php
                    $grandTotal = collect($selectedItems)->sum(fn($i) => $i->product->price * $i->quantity);
                @endphp
                <span class="mr-2">Order Total ({{ collect($selectedItems)->sum('quantity') }} items):</span>
                <span class="text-lg font-bold">₱{{ number_format($grandTotal, 2) }}</span>
            </div>


    <div class="border border-gray-200 rounded-xl p-5">
    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf

        <!-- Name -->
        <div class="flex items-center mb-3">
            <span class="w-32 text-gray-700 font-medium">Name:</span>
            <input type="text" name="guest_name" class="flex-1 border rounded p-2" required>
        </div>

        <!-- Contact Info -->
        <div class="flex items-center mb-3">
            <span class="w-32 text-gray-700 font-medium">Email:</span>
            <input type="email" name="guest_email" class="flex-1 border rounded p-2" required>
        </div>
        <div class="flex items-center mb-3">
            <span class="w-32 text-gray-700 font-medium">Phone:</span>
            <input type="text" name="guest_phone" class="flex-1 border rounded p-2" required>
        </div>
        <div class="flex items-center mb-3">
            <span class="w-32 text-gray-700 font-medium">Address:</span>
            <input type="text" name="address" class="flex-1 border rounded p-2" required>
        </div>

        <!-- Payment Methods -->
        <div class="flex items-center mb-6">
            <p class="w-40 text-gray-700 font-medium">Payment Method:</p>
            <div class="flex gap-4">
                <!-- Hidden input to store selected method -->
                <input type="hidden" name="payment_method" id="payment_method" required>

                <!-- PayPal button -->
                <button 
                    type="button" 
                    onclick="selectPayment('PayPal', this)" 
                    class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500">
                    PayPal
                </button>

                <!-- Cash On Pickup button -->
                <button 
                    type="button" 
                    onclick="selectPayment('Cash on Pickup', this)" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">
                    Cash On Pickup
                </button>
            </div>
        </div>
    </div>
        

        <!-- Total Payment -->
        <div class="flex justify-end mb-4">
            <span class="text-gray-700 mr-2">Total Payment:</span>
            <span class="text-pink-600 font-bold text-lg">
                ₱{{ number_format($grandTotal, 2) }}
            </span>
        </div>

        <!-- Checkout Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="bg-pink-400 hover:bg-pink-600 text-white px-6 py-3 rounded font-semibold transition">
                Check Out
            </button>
        </div>
    </form>
</div>

<script>
    function selectPayment(method, btn) {
        // Set hidden input value
        document.getElementById('payment_method').value = method;

        // Reset button styles
        document.querySelectorAll('[onclick^="selectPayment"]').forEach(b => {
            b.classList.remove('bg-yellow-400', 'text-gray-900');
            b.classList.add('bg-gray-200', 'text-gray-700');
        });

        // Highlight selected button
        btn.classList.remove('bg-gray-200', 'text-gray-700');
        btn.classList.add('bg-yellow-400', 'text-gray-900');
    }
</script>


</body>
</html>
