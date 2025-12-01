<x-dashboardlayout>
    <div x-data="orderModal()" class="p-6 h-[90%]">
        <h2 class="text-2xl font-semibold mb-6">Checkout Details</h2>

        <div class="bg-white rounded-lg shadow mb-3 h-[80%] flex flex-col">
            <!-- Table Header -->
            <div class="overflow-x-auto border-b border-gray-200">
                <table class="min-w-full">
                    <colgroup>
                        <col class="w-[40%]">   <!-- Component -->
                        <col class="w-[20%]">   <!-- Category -->
                        <col class="w-[20%]">   <!-- Qty -->
                        <col class="w-[20%]">   <!-- Price -->
                    </colgroup>
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Component</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Category</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700 border-b">Qty</th>
                            <th class="px-6 py-3 text-sm font-medium text-gray-700 border-b">Price</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto overflow-x-auto">
                <table class="min-w-full h-full">
                    <colgroup>
                        <col class="w-[40%]">   <!-- Component -->
                        <col class="w-[20%]">   <!-- Category -->
                        <col class="w-[20%]">   <!-- Qty -->
                        <col class="w-[20%]">   <!-- Price -->
                    </colgroup>
                    <tbody class="align-top">
                        @forelse ($paginatedGroups as $index => $group)
                            @foreach ($group['cart_items'] as $cartItem)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b">
                                        @php
                                            $model = 'Unknown';
                                            switch($cartItem->product_type) {
                                                case 'case': $model = $cartItem->case?->brand . ' ' . $cartItem->case?->model ?? 'N/A'; break;
                                                case 'cpu': $model = $cartItem->cpu?->brand . ' ' . $cartItem->cpu?->model ?? 'N/A'; break;
                                                case 'gpu': $model = $cartItem->gpu?->brand . ' ' . $cartItem->gpu?->model ?? 'N/A'; break;
                                                case 'motherboard': $model = $cartItem->motherboard?->brand . ' ' . $cartItem->motherboard?->model ?? 'N/A'; break;
                                                case 'ram': $model = $cartItem->ram?->brand . ' ' . $cartItem->ram?->model ?? 'N/A'; break;
                                                case 'storage': $model = $cartItem->storage?->brand . ' ' . $cartItem->storage?->model ?? 'N/A'; break;
                                                case 'psu': $model = $cartItem->psu?->brand . ' ' . $cartItem->psu?->model ?? 'N/A'; break;
                                                case 'cooler': $model = $cartItem->cooler?->brand . ' ' . $cartItem->cooler?->model ?? 'N/A'; break;
                                            }
                                        @endphp
                                        {{ $model }}
                                    </td>
                                    <td class="px-6 py-4 border-b">{{ ucfirst($cartItem->product_type) }}</td>
                                    <td class="px-6 py-4 border-b text-center">{{ $cartItem->quantity }}</td>
                                    <td class="px-6 py-4 border-b text-center">₱{{ number_format($cartItem->total_price, 2) }}</td>
                                </tr>
                            @endforeach

                            {{-- TOTAL EACH ORDER --}}
                            <tr class="bg-gray-50 h-16">
                                <td colspan="3" class="px-6 border-b text-right font-semibold align-middle">
                                    Total:
                                </td>
                                <td class="px-6 border-b text-center font-bold text-gray-700 align-middle">
                                    ₱{{ number_format($group['total_cost'], 2) }}
                                </td>
                            </tr>

                            {{-- ORDER STATUS BUTTON --}}
                            <tr class="bg-gray-50 h-16">
                                <td colspan="4" class="px-6 border-b align-middle">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs">Disclaimer: Please note that receipts for product purchases are only available upon pick-up.</p>
                                        <button @click="setSelectedOrder({{ $index }})"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                            Order Status
                                        </button>
                                    </div>
                                </td>
                            </tr>



                            <tr>
                                <td colspan="4" class="px-6 py-2 bg-gray-100"></td>
                            </tr>
                        @empty
                            <tr class="h-full">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 align-middle">
                                    No checkout items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Order Status Modal -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[99999] p-4">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative overflow-hidden border border-gray-200">
                
                <!-- Header with Gradient -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">Order Status</h3>
                            <p class="text-blue-100 text-sm mt-1" x-text="'Order #' + (selectedOrder?.shopping_cart_id || '')"></p>
                        </div>
                        <button @click="showModal = false" 
                                class="text-white hover:text-gray-200 transition-colors duration-200 p-2 rounded-full hover:bg-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="max-h-[70vh] overflow-y-auto p-6">
                    <!-- Order Information Card -->
                    <div class="bg-gray-50 rounded-xl p-5 mb-6 border border-gray-200" x-show="selectedOrder">
                        <h4 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Order Details
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Order ID:</span>
                                    <span x-text="'#' + selectedOrder.checkout_id" class="text-gray-800 font-semibold bg-white px-3 py-1 rounded-lg border text-sm">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Checkout Date:</span>
                                    <span x-text="formatDate(selectedOrder.checkout_date)" class="text-gray-800 font-medium">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Customer Name:</span>
                                    <span x-text="selectedOrder.user ? selectedOrder.user.first_name + ' ' + selectedOrder.user.last_name : 'Unknown'" class="text-gray-800 font-medium">-</span>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Payment Method:</span>
                                    <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-800 font-medium bg-white px-3 py-1 rounded-lg border text-sm">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Contact:</span>
                                    <span x-text="selectedOrder.user?.phone_number || '0917-XXX-XXXX'" class="text-gray-800 font-medium">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Badge -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Payment Status:</span>
                                <span class="font-semibold px-4 py-2 rounded-full text-sm border-2" 
                                    :class="{
                                        'bg-green-100 text-green-800 border-green-200': selectedOrder.is_downpayment == 0,
                                        'bg-blue-100 text-blue-800 border-blue-200': selectedOrder.is_downpayment,
                                    }"
                                    x-text="selectedOrder.is_downpayment ? 'Downpayment Paid' : (selectedOrder.payment_status || 'Paid')">-</span>
                            </div>
                        </div>

                        <!-- Payment Amount Information -->
                        <template x-if="selectedOrder.is_downpayment">
                            <div class="mt-4 pt-4 border-t border-gray-200 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Downpayment Paid:</span>
                                    <span x-text="'₱' + formatCurrency(selectedOrder.downpayment_amount)" class="text-green-600 font-bold text-lg">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Remaining Balance:</span>
                                    <span x-text="'₱' + formatCurrency(selectedOrder.remaining_balance)" class="text-orange-600 font-bold text-lg">-</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Order Amount:</span>
                                    <span x-text="'₱' + formatCurrency(selectedOrder.total_cost)" class="text-purple-600 font-bold text-xl">-</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!selectedOrder.is_downpayment">
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Total Amount:</span>
                                    <span x-text="'₱' + formatCurrency(selectedOrder.total_cost)" class="text-purple-600 font-bold text-xl">-</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Payment Instructions for Downpayment -->
                    <div x-show="selectedOrder && selectedOrder.is_downpayment" 
                        class="mb-6 p-5 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl shadow-sm">
                        <div class="flex items-start">
                            <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-yellow-800 mb-2 text-lg">Payment Reminder</h4>
                                <p class="text-yellow-700">
                                    You have paid <span x-text="'₱' + formatCurrency(selectedOrder.downpayment_amount)" class="font-bold"></span> 
                                    (50% downpayment). The remaining balance of 
                                    <span x-text="'₱' + formatCurrency(selectedOrder.remaining_balance)" class="font-bold"></span> 
                                    must be settled upon pickup.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-lg text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Order Timeline
                        </h4>
                        
                        <div class="space-y-6">
                            <!-- Submitted Status -->
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-1 h-12 bg-gradient-to-b from-green-200 to-gray-100" 
                                        x-show="selectedOrder.pickup_status === 'Pending' || selectedOrder.is_downpayment"></div>
                                </div>
                                <div class="flex-1 pb-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Submitted - ' + formatDate(selectedOrder.checkout_date)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Your order has been placed successfully and is being processed.</p>
                                    <template x-if="selectedOrder.is_downpayment">
                                        <p class="text-blue-600 text-sm font-medium mt-2 bg-blue-50 px-3 py-2 rounded-lg">
                                            ✅ 50% downpayment of <span x-text="'₱' + formatCurrency(selectedOrder.downpayment_amount)" class="font-bold"></span> received.
                                        </p>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- To Pick Up Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.pickup_status === 'Pending'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-1 h-12 bg-gradient-to-b from-green-200 to-gray-100"></div>
                                </div>
                                <div class="flex-1 pb-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Ready for Pickup - ' + formatDate(selectedOrder.updated_at)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Your order is ready to be picked up from our shop location.</p>
                                    <template x-if="selectedOrder.is_downpayment">
                                        <p class="text-orange-600 text-sm font-medium mt-2 bg-orange-50 px-3 py-2 rounded-lg">
                                            💰 Please bring <span x-text="'₱' + formatCurrency(selectedOrder.remaining_balance)" class="font-bold"></span> for the remaining balance.
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <!-- Completed Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.pickup_status === 'Completed'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Completed - ' + formatDate(selectedOrder.updated_at)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Order has been successfully completed and picked up.</p>
                                    <template x-if="selectedOrder.is_downpayment">
                                        <p class="text-green-600 text-sm font-medium mt-2 bg-green-50 px-3 py-2 rounded-lg">
                                            🎉 Full payment of <span x-text="'₱' + formatCurrency(selectedOrder.total_cost)" class="font-bold"></span> received.
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Status Banner -->
                    <div class="mt-6 p-5 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 text-center">
                        <p class="font-bold text-lg mb-2">
                            Current Status: 
                            <template x-if="selectedOrder.pickup_status === null && !selectedOrder.is_downpayment">
                                <span class="text-blue-600 ml-2">📦 Submitted</span>
                            </template>
                            <template x-if="selectedOrder.is_downpayment">
                                <span class="text-purple-600 ml-2">💳 Downpayment Paid - Awaiting Pickup</span>
                            </template>
                            <template x-if="selectedOrder.pickup_status === 'Pending'">
                                <span class="text-green-600 ml-2">✅ Ready for Pickup</span>
                            </template>
                            <template x-if="selectedOrder.pickup_status === 'Completed'">
                                <span class="text-gray-600 ml-2">🎉 Completed</span>
                            </template>
                        </p>
                        <template x-if="selectedOrder.is_downpayment">
                            <p class="text-sm text-gray-600 mt-2">
                                Remaining balance to pay upon pickup: 
                                <span x-text="'₱' + formatCurrency(selectedOrder.remaining_balance)" class="font-bold text-orange-600 text-lg"></span>
                            </p>
                        </template>
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

                 // ADD THIS MISSING FUNCTION
                formatCurrency(amount) {
                    if (amount === null || amount === undefined || amount === '') {
                        return '0.00';
                    }
                    // Convert to number and format
                    const num = parseFloat(amount);
                    if (isNaN(num)) {
                        return '0.00';
                    }
                    return num.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
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