<x-dashboardlayout>
    <div x-data="{
        showModal: false, 
        selectedOrder: {},
        formatDate(dateString, format = 'default') {
            if (!dateString) return 'Date unavailable';
            
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Invalid date';
                
                if (format === 'Y-m-d') {
                    return date.toISOString().split('T')[0]; // Returns '2024-01-15'
                } else {
                    return date.toLocaleDateString('en-US', { 
                        month: '2-digit', 
                        day: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }).replace(',', ' at');
                }
            } catch (error) {
                return 'Date error';
            }
        },
        formatCurrency(amount) {
            if (!amount && amount !== 0) return '0.00';
            
            const numAmount = Number(amount);
            if (isNaN(numAmount)) return '0.00';
            
            return numAmount.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }" class="p-6 h-[90%]">
        <h2 class="text-2xl font-semibold mb-6">Order Details</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow mb-3 ">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Build Name</th>
                        <th class="px-6 py-3 border-b text-right text-sm font-medium text-gray-700">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 border-b">{{ $order->userBuild->build_name }}</td>
                            <td class="px-6 py-4 border-b text-right">₱{{ number_format($order->userBuild->total_price, 2) }}</td>
                        </tr>
                        
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-6 py-3 border-b text-right">
                                <div class="font-semibold text-gray-700 flex items-center justify-between">
                                    <p class="text-xs font-normal text-black">Disclaimer: Please note that receipts for product purchases are only available upon pick-up.</p>
                                    <button @click="showModal = true; selectedOrder = {{ $order->toJson() }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        Order Status
                                    </button>
                                </div>
                            </td>
                        </tr>


                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Order Status Modal -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-[99999] p-4">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white">Order Status</h3>
                        <button @click="showModal = false" 
                                class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200 transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Order Information -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6 border border-gray-200">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-300">Order Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Checkout ID:</span>
                                <span x-text="'#' + selectedOrder.user_build?.user_id" class="text-blue-600 font-mono font-bold">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Checkout Date:</span>
                                <span x-text="formatDate(selectedOrder.created_at)" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Customer Name:</span>
                                <span x-text="selectedOrder.user_build?.user ? selectedOrder.user_build.user.first_name + ' ' + (selectedOrder.user_build.user.middle_name || '') + ' ' + selectedOrder.user_build.user.last_name : 'Unknown'" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Payment Method:</span>
                                <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Contact Number:</span>
                                <span x-text="selectedOrder.user_build?.user?.phone_number || '0917-XXX-XXXX'" class="text-gray-800 font-medium">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- COMPONENTS -->
                    <div class="bg-white rounded-xl p-6 mb-6 border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-300">Components</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Case:</span>
                                <span x-text="selectedOrder.user_build?.case?.brand + ' ' + selectedOrder.user_build?.case?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">CPU:</span>
                                <span x-text="selectedOrder.user_build?.cpu?.brand + ' ' + selectedOrder.user_build?.cpu?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">RAM:</span>
                                <span x-text="selectedOrder.user_build?.ram?.brand + ' ' + selectedOrder.user_build?.ram?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Storage:</span>
                                <span x-text="selectedOrder.user_build?.storage?.brand + ' ' + selectedOrder.user_build?.storage?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Motherboard:</span>
                                <span x-text="selectedOrder.user_build?.motherboard?.brand + ' ' + selectedOrder.user_build?.motherboard?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">GPU:</span>
                                <span x-text="selectedOrder.user_build?.gpu?.brand + ' ' + selectedOrder.user_build?.gpu?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">PSU:</span>
                                <span x-text="selectedOrder.user_build?.psu?.brand + ' ' + selectedOrder.user_build?.psu?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Cooler:</span>
                                <span x-text="selectedOrder.user_build?.cooler?.brand + ' ' + selectedOrder.user_build?.cooler?.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                        </div>
                        
                        <!-- Total Price -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center py-3 px-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                                <span class="font-bold text-gray-800 text-lg">Total Price:</span>
                                <span x-text="'₱' + formatCurrency(selectedOrder.user_build?.total_price)" class="text-green-600 font-bold text-xl">-</span>
                            </div>
                        </div>

                        <!-- Payment Status Badge -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Payment Status:</span>
                                <span class="font-semibold px-4 py-2 rounded-full text-sm border-2" 
                                    :class="{
                                        'bg-green-100 text-green-800 border-green-200': selectedOrder.payment_status === 'Paid',
                                        'bg-blue-100 text-blue-800 border-blue-200': selectedOrder.is_downpayment,
                                        'bg-yellow-100 text-yellow-800 border-yellow-200': selectedOrder.payment_status === 'Pending',
                                        'bg-red-100 text-red-800 border-red-200': selectedOrder.payment_status === 'Cancelled'
                                    }"
                                    x-text="selectedOrder.is_downpayment ? 'Downpayment Paid' : (selectedOrder.payment_status || 'Pending')">-</span>
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
                                    <span x-text="'₱' + formatCurrency(selectedOrder.user_build?.total_price)" class="text-purple-600 font-bold text-xl">-</span>
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
                            <!-- Submitted Status (Always shown) -->
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-1 h-12 bg-gradient-to-b from-green-200 to-gray-100" 
                                        x-show="selectedOrder.status === 'Approved' || (selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending')"></div>
                                </div>
                                <div class="flex-1 pb-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Submitted - ' + formatDate(selectedOrder.created_at)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Your order has been placed successfully and is being processed.</p>
                                    <template x-if="selectedOrder.is_downpayment">
                                        <p class="text-blue-600 text-sm font-medium mt-2 bg-blue-50 px-3 py-2 rounded-lg">
                                            ✅ 50% downpayment of <span x-text="'₱' + formatCurrency(selectedOrder.downpayment_amount)" class="font-bold"></span> received.
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <!-- Approved Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.status === 'Approved'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-1 h-12 bg-gradient-to-b from-green-200 to-gray-100" 
                                        x-show="selectedOrder.pickup_status === 'Pending'"></div>
                                </div>
                                <div class="flex-1 pb-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Approved - ' + formatDate(selectedOrder.updated_at)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Order has been approved and is currently being assembled by our technicians.</p>
                                </div>
                            </div>
                            
                            <!-- Ready for Pickup Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-1 h-12 bg-gradient-to-b from-green-200 to-gray-100" 
                                        x-show="selectedOrder.pickup_status === 'Completed'"></div>
                                </div>
                                <div class="flex-1 pb-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-semibold text-gray-800" x-text="'Ready for Pickup - ' + formatDate(selectedOrder.updated_at)"></span>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">Your PC build is complete and ready to be picked up from our shop location.</p>
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
                            <template x-if="selectedOrder.status === 'Pending'">
                                <span class="text-blue-600 ml-2">📦 Submitted</span>
                            </template>
                            <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status !== 'Pending' && !selectedOrder.is_downpayment">
                                <span class="text-yellow-600 ml-2">✅ Approved</span>
                            </template>
                            <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status !== 'Pending' && selectedOrder.is_downpayment">
                                <span class="text-purple-600 ml-2">💳 Downpayment Paid - Awaiting Pickup</span>
                            </template>
                            <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                                <span class="text-green-600 ml-2">✅ Ready for Pickup</span>
                            </template>
                            <template x-if="selectedOrder.pickup_status === 'Completed'">
                                <span class="text-gray-600 ml-2">🎉 Completed</span>
                            </template>
                        </p>
                        <template x-if="selectedOrder.is_downpayment && selectedOrder.pickup_status !== 'Completed'">
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
    {{ $orders->links() }}
</x-dashboardlayout>