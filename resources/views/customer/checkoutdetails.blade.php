<x-dashboardlayout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold mb-6">Checkout Details</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
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
                    @forelse ($groupedCheckouts as $index => $group)
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
                                    <button onclick="openModal({{ $index }})" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        Order Status
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
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No checkout items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Status Modal -->
    <div id="orderStatusModal" 
            class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[99999] overflow-y-scroll">
        <div class="bg-white w-full max-w-2xl m-4 p-8 rounded-lg shadow-xl relative h-[80%] overflow-y-scroll">
            <button onclick="closeModal()" 
                    class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✖</button>

            <h3 class="text-2xl font-bold mb-6 text-center">Order Status</h3>

            <!-- Order Information -->
            <div class="mb-6 space-y-2">
                <div class="flex justify-between">
                    <span class="font-semibold">Checkout ID:</span>
                    <span id="modalCartId" class="text-gray-700">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Checkout Date:</span>
                    <span id="modalCheckoutDate" class="text-gray-700">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Customer Name:</span>
                    <span id="modalCustomerName" class="text-gray-700">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Payment Method:</span>
                    <span id="modalPaymentMethod" class="text-gray-700">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Contact Number:</span>
                    <span id="modalContactNumber" class="text-gray-700">0917-XXX-XXXX</span>
                </div>
            </div>

            <hr class="my-6 border-gray-300">

            <!-- Status Timeline -->
            <h4 class="font-bold text-lg mb-4">Status Timeline</h4>
            <div class="space-y-4">
                <!-- Timeline Item 1 -->
                <div class="flex items-start space-x-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                        <div class="w-0.5 h-8 bg-gray-300"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-sm">04/14/25 10:23 AM - Submitted</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Your order has been placed successfully.</p>
                    </div>
                </div>
                
                <!-- Timeline Item 2 -->
                <div class="flex items-start space-x-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                        <div class="w-0.5 h-8 bg-gray-300"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-sm">04/14/25 11:10 AM - Pending Approval</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Order is being reviewed by our team.</p>
                    </div>
                </div>
                
                <!-- Timeline Item 3 -->
                <div class="flex items-start space-x-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                        <div class="w-0.5 h-8 bg-gray-300"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-sm">04/14/25 02:35 PM - Approved</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Order approved and queued for shipping.</p>
                    </div>
                </div>
                
                <!-- Timeline Item 4 -->
                <div class="flex items-start space-x-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-sm">04/14/25 02:35 PM - To Pick Up</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Your order is ready to be picked up from the shop.</p>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-300">

            <!-- Current Status -->
            <div class="text-center">
                <p class="font-bold text-lg">
                    Current Status: 
                    <span id="modalCurrentStatus" class="text-green-600">To Pick Up</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Store the grouped checkouts data in JavaScript
        const groupedCheckouts = @json($groupedCheckouts);
        
        console.log('Grouped Checkouts:', groupedCheckouts); // Debug: Check if data exists

        function openModal(index) {
            console.log('Button clicked, index:', index); // Debug: Check if function is called
            console.log('Group data:', groupedCheckouts[index]); // Debug: Check data
            
            const group = groupedCheckouts[index];
            const modal = document.getElementById('orderStatusModal');
            
            if (modal && group) {
                console.log('Updating modal with data'); // Debug
                
                // Update modal content with the selected group data
                document.getElementById('modalCartId').textContent = '#' + group.shopping_cart_id;
                document.getElementById('modalCheckoutDate').textContent = new Date(group.checkout_date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric'
                });
                
                // Handle customer name safely
                let customerName = 'Unknown User';
                if (group.user && typeof group.user === 'object') {
                    customerName = (group.user.first_name || '') + ' ' + (group.user.last_name || '');
                    customerName = customerName.trim() || 'Unknown User';
                }
                document.getElementById('modalCustomerName').textContent = customerName;
                
                document.getElementById('modalPaymentMethod').textContent = group.payment_method || 'N/A';
                document.getElementById('modalCurrentStatus').textContent = group.pickup_status || 'Pending';
                document.getElementById('modalContactNumber').textContent = group.user.phone_number;
                
                modal.classList.remove('hidden');
            } else {
                console.error('Modal not found or invalid group data');
            }
        }

        function closeModal() {
            const modal = document.getElementById('orderStatusModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Make functions globally available
        window.openModal = openModal;
        window.closeModal = closeModal;
    </script>
</x-dashboardlayout>