<x-dashboardlayout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold mb-6">Order Details</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Order ID</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Details</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Date Ordered</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Verification Status</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Date Approved</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Total Amount</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Pickup Date</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Build Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 border-b">#ORD{{ $order->id }}</td>
                            <td class="px-6 py-4 border-b">
                                <a href="#" class="text-blue-600 hover:underline">View</a>
                            </td>
                            <td class="px-6 py-4 border-b">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 border-b">{{ ucfirst($order->status ?? 'Pending') }}</td>
                            <td class="px-6 py-4 border-b">{{ optional($order->approved_at)->format('Y-m-d') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 border-b">₱{{ number_format($order->total ?? 0, 2) }}</td>
                            <td class="px-6 py-4 border-b">{{ $order->pickup_date ?? 'N/A' }}</td>
                            <td class="px-6 py-4 border-b">{{ $order->build_status ?? 'Pending' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dashboardlayout>