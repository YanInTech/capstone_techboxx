<x-dashboardlayout>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-6">Purchased History</h2>

        @forelse($orders as $order)
            <div class="mb-8 border-b pb-4">

                {{-- Date aligned right --}}
                <div class="flex justify-end mb-2">
                    <p class="text-sm text-gray-500">Date: {{ $order->created_at->format('F d, Y') }}</p>
                </div>

                <table class="w-full text-left mb-3">
                    <thead>
                        <tr class="border-b">
                            <th class="py-1">Component</th>
                            <th class="py-1">Category</th>
                            <th class="py-1 text-center">Qty</th>
                            <th class="py-1 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $component = $item->name ?? 'Unknown';
                                $category  = 'N/A';

                                if (!empty($item->product_type) && !empty($item->product_id) && isset($hardwareMap[$item->product_type])) {
                                    $modelClass = $hardwareMap[$item->product_type];
                                    if (class_exists($modelClass)) {
                                        try {
                                            $product = $modelClass::find($item->product_id);
                                            if ($product) {
                                                $component = trim(($product->brand ?? '') . ' ' . ($product->model ?? '') . ' ' . ($product->name ?? ''));
                                                $category = $product->category->name ?? ucfirst($item->product_type);
                                            }
                                        } catch (\Exception $e) {
                                            // fallback if lookup fails
                                        }
                                    }
                                }
                            @endphp

                            <tr class="border-b">
                                <td class="py-1">{{ $component }}</td>
                                <td class="py-1">{{ $category }}</td>
                                <td class="py-1 text-center">{{ $item->quantity }}</td>
                                <td class="py-1 text-right">₱{{ number_format($item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Total above + Button below (aligned right) --}}
                <div class="flex flex-col items-end space-y-2">
                    <p class="font-semibold">
                        Total: ₱{{ number_format($order->total ?? $order->total_amount ?? $order->total_price ?? 0, 2) }}
                    </p>
                    <a href="{{ route('customer.invoice.show', $order->id) ?? '#' }}"
                       class="px-4 py-2 bg-white border border-black text-black rounded hover:bg-gray-100 text-sm">
                        View Invoice
                    </a>
                </div>
            </div>
        @empty
            <p class="text-gray-600">No purchased history found.</p>
        @endforelse
    </div>
</x-dashboardlayout>