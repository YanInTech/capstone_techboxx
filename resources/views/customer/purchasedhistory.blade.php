<x-dashboardlayout>
    <div x-data="orderModal()" class="p-6 h-[85%]">
        <h2 class="text-2xl font-semibold mb-6">Checkout Details</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow mb-3 h-[80%] overflow-y-scroll">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Component</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Category</th>
                        <th class="px-6 py-3 border-b text-center text-sm font-medium text-gray-700">Qty</th>
                        <th class="px-6 py-3 border-b text-right text-sm font-medium text-gray-700">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paginatedGroups as $index => $group)
                        {{-- Order Items --}}
                        @foreach ($group['cart_items'] as $cartItem)
                            <tr>
                                <td class="px-6 py-4 border-b">
                                    @php
                                        $model = 'Unknown';
                                        
                                        switch($cartItem->product_type) {
                                            case 'case':
                                                $model = $cartItem->case ? $cartItem->case->brand . ' ' . $cartItem->case->model : 'N/A';
                                                break;
                                            case 'cpu':
                                                $model = $cartItem->cpu ? $cartItem->cpu->brand . ' ' . $cartItem->cpu->model : 'N/A';
                                                break;
                                            case 'gpu':
                                                $model = $cartItem->gpu ? $cartItem->gpu->brand . ' ' . $cartItem->gpu->model : 'N/A';
                                                break;
                                            case 'motherboard':
                                                $model = $cartItem->motherboard ? $cartItem->motherboard->brand . ' ' . $cartItem->motherboard->model : 'N/A';
                                                break;
                                            case 'ram':
                                                $model = $cartItem->ram ? $cartItem->ram->brand . ' ' . $cartItem->ram->model : 'N/A';
                                                break;
                                            case 'storage':
                                                $model = $cartItem->storage ? $cartItem->storage->brand . ' ' . $cartItem->storage->model : 'N/A';
                                                break;
                                            case 'psu':
                                                $model = $cartItem->psu ? $cartItem->psu->brand . ' ' . $cartItem->psu->model : 'N/A';
                                                break;
                                            case 'cooler':
                                                $model = $cartItem->cooler ? $cartItem->cooler->brand . ' ' . $cartItem->cooler->model : 'N/A';
                                                break;
                                        }
                                    @endphp
                                    {{ $model }}
                                </td>
                                <td class="px-6 py-4 border-b">{{ ucfirst($cartItem->product_type) }}</td>
                                <td class="px-6 py-4 border-b text-center">{{ $cartItem->quantity }}</td>
                                <td class="px-6 py-4 border-b text-right">₱{{ number_format($cartItem->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                        
                        {{-- TOTAL EACH ORDERS --}}
                        <tr class="bg-gray-50">
                            <td colspan="4" class="px-6 py-3 border-b text-right">
                                <div class="font-semibold text-gray-700">
                                    Total: ₱{{ number_format($group['total_cost'], 2) }}
                                </div>
                            </td>
                        </tr>

                        {{-- ORDER STATUS BUTTON --}}
                        <tr class="bg-gray-50">
                            <td colspan="4" class="px-6 py-3 border-b text-right">
                                <div class="font-semibold text-gray-700">
                                    <button @click="setSelectedOrder({{ $index }})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        View Invoice
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        {{-- Empty space between orders --}}
                        <tr>
                            <td colspan="4" class="px-6 py-2 bg-gray-100"></td>
                        </tr>
                    @empty
                        <tr>
                            <p class="text-gray-600">No purchased history found.</p>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Order Status Modal -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[99999]">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-2xl m-4 p-8 rounded-lg shadow-xl relative overflow-y-scroll">
                <button @click="showModal = false" 
                        class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✖</button>

                <h3 class="text-2xl font-bold mb-6 text-center">Order Invoice</h3>

                <!-- Order Information -->
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Order ID:</span>
                        <span x-text="'#' + selectedOrder.shopping_cart_id" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Checkout Date:</span>
                        <span x-text="formatDate(selectedOrder.checkout_date)" class="text-gray-700">-</span>
                    </div>
                </div>

                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Customer Information</h4>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Customer Name:</span>
                        <span x-text="selectedOrder.user ? selectedOrder.user.first_name + ' ' + selectedOrder.user.last_name : 'Unknown'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Payment Method:</span>
                        <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Contact Number:</span>
                        <span x-text="selectedOrder.user?.phone_number || '0917-XXX-XXXX'" class="text-gray-700">-</span>
                    </div>
                </div>
                
                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Fulfillment Method: In-Store Pickup</h4>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Pickup Location:</span>
                        <span class="text-gray-700">Madoxx.QWE</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Address:</span>
                        <span class="text-gray-700">Pardo, Cebu City</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Picked Up Date:</span>
                        <span x-text="formatDate(selectedOrder.pickup_date)" class="text-gray-700">-</span>
                    </div>
                </div>

                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Order Summary:</h4>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Pickup Location:</span>
                        <span class="text-gray-700">Madoxx.QWE</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Address:</span>
                        <span class="text-gray-700">Pardo, Cebu City</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Picked Up Date:</span>
                        <span x-text="formatDate(selectedOrder.pickup_date)" class="text-gray-700">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{ $paginatedGroups->links() }}


    <script>
        function orderModal() {
            return {
                showModal: false,
                selectedOrder: null,
                paginatedGroups: @json($paginatedGroups->items()),
                
                setSelectedOrder(index) {
                    console.log('Setting selected order:', index);
                    console.log('Available orders:', this.paginatedGroups);
                    
                    if (this.paginatedGroups[index]) {
                        this.selectedOrder = this.paginatedGroups[index];
                        this.showModal = true;
                        console.log('Selected order data:', this.selectedOrder);
                    } else {
                        console.error('Order not found at index:', index);
                    }
                },
                
                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                
                getComponentModel(item) {
                    if (!item) return 'Unknown';
                    
                    const productType = item.product_type;
                    const productData = item[productType];
                    
                    if (productData && productData.brand && productData.model) {
                        return productData.brand + ' ' + productData.model;
                    }
                    
                    return 'Unknown Component';
                }
            }
        }
    </script>
</x-dashboardlayout>