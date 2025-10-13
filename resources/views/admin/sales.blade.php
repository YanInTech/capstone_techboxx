<x-dashboardlayout>
    <div class="p-6">
        <!-- Page Header -->
        <div class="flex items-center gap-3 mb-6">
            <h2 class="text-2xl font-semibold">Sales Report ({{ ucfirst($period) }})</h2>

            <form method="GET" action="{{ route('admin.sales') }}">
                <select name="period"
                        class="border rounded px-3 pr-8 py-1 text-sm appearance-none bg-white
                               bg-[url('data:image/svg+xml;utf8,<svg fill=\'%23000\' height=\'20\' viewBox=\'0 0 20 20\' width=\'20\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M5.516 7.548l4.484 4.482 4.484-4.482L16 9.064l-6 6-6-6z\'/></svg>')] bg-no-repeat bg-[right_0.5rem_center] cursor-pointer"
                        onchange="this.form.submit()">
                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="annually" {{ $period == 'annually' ? 'selected' : '' }}>Annually</option>
                </select>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm mb-1">{{ ucfirst($period) }} Orders</h3>
                <p class="text-2xl font-semibold">{{ number_format($ordersCount) }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm mb-1">Cost of Goods Sold</h3>
                <p class="text-2xl font-semibold">₱{{ number_format($costOfGoods) }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm mb-1">{{ ucfirst($period) }} Revenue</h3>
                <p class="text-2xl font-semibold">₱{{ number_format($totalRevenue, 2) }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm mb-1">Profit</h3>
                <p class="text-2xl font-semibold text-green-600">₱{{ number_format($profit, 2) }}</p>
            </div>
        </div>

        <!-- Top Selling + Earnings Report -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <!-- Top Selling Products -->
            <div class="bg-white p-4 rounded-lg shadow col-span-1">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Top Selling Products</h3>
                    <button class="text-blue-500 text-sm">View All</button>
                </div>

                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2">Product</th>
                            <th class="py-2 text-center">Sold</th>
                            <th class="py-2 text-center">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topProducts as $product)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2">{{ $product->name }}</td>
                                <td class="py-2 text-center">{{ $product->total_sold }}</td>
                                <td class="py-2 text-center">₱{{ number_format($product->earnings, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Earnings Report -->
            <div class="bg-white p-4 rounded-lg shadow col-span-2">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Earnings Report</h3>
                    <div class="text-xs text-gray-400">Last Month vs Current</div>
                </div>
                <canvas id="earningsChart" height="120"></canvas>
            </div>
        </div>

        <!-- Pie Chart + Product Orders + Cart Analysis -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <!-- Order Reports Pie -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold mb-3">Order Reports</h3>
                <canvas id="orderPieChart" height="180"></canvas>
            </div>

            <!-- Product Orders -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold mb-3">Product Orders</h3>
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2">Type</th>
                            <th class="py-2 text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productOrders as $row)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2">{{ $row['type'] }}</td>
                                <td class="py-2 text-center">{{ $row['orders'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cart Analysis -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold mb-3">Cart Analysis</h3>
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2">Type</th>
                            <th class="py-2">Product Name</th>
                            <th class="py-2 text-center">Add to Cart</th>
                            <th class="py-2 text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cartAnalysis as $cart)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2">{{ $cart['type'] }}</td>
                                <td class="py-2">{{ $cart['product'] }}</td>
                                <td class="py-2 text-center">{{ $cart['added_to_cart'] }}</td>
                                <td class="py-2 text-center">{{ $cart['orders'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Frequent Product Bought Together -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg shadow col-span-2">
                <h3 class="font-semibold mb-3">Frequent Product Bought Together</h3>
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2">Product A</th>
                            <th class="py-2">Product B</th>
                            <th class="py-2 text-center">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($frequentPairs as $pair)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2">{{ $pair->product_a }}</td>
                                <td class="py-2">{{ $pair->product_b }}</td>
                                <td class="py-2 text-center">₱{{ number_format($pair->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-4 rounded-lg shadow flex flex-col justify-center items-center">
                <h3 class="font-semibold mb-2">Active Users</h3>
                <p class="text-3xl font-semibold">{{ number_format($activeUsers) }}</p>
                <p class="text-gray-400 text-sm">Last 30 days</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        const earningsCtx = document.getElementById('earningsChart').getContext('2d');
        new Chart(earningsCtx, {
            type: 'line',
            data: {
                labels: ['Dec','Jan','Feb','Mar','Apr','May','Jun'],
                datasets: [
                    { label: 'Last Month', data: [40,60,30,70,50,80,45], fill: false, tension: 0.3, borderColor: '#60A5FA' },
                    { label: 'Current', data: [50,40,70,50,80,60,90], fill: false, tension: 0.3, borderColor: '#34D399' }
                ]
            }
        });

        const pieCtx = document.getElementById('orderPieChart').getContext('2d');
        const labels = {!! json_encode($productOrders->pluck('type')) !!};
        const dataVals = {!! json_encode($productOrders->pluck('orders')) !!};
        const total = dataVals.reduce((sum, val) => sum + val, 0);

        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataVals,
                    backgroundColor: ['#60A5FA','#34D399','#FBBF24','#F87171','#A78BFA','#E879F9','#CBD5E1','#FCA5A5']
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} orders (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 13 },
                        formatter: (value) => ((value / total) * 100).toFixed(1) + '%'
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
</x-dashboardlayout>