<x-dashboardlayout>
    <h2>Activity Logs</h2>

    {{-- FILTERS --}}
    <section class="section-style !pl-0 mb-6">
        <form method="GET" action="{{ route('admin.activitylogs') }}" class="space-y-4">
            {{-- Filter Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Month Filter --}}
                <div class="space-y-2">
                    <label for="month_filter" class="block text-sm font-medium text-gray-700">Month</label>
                    <select 
                        id="month_filter" 
                        name="month"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                    >
                        <option value="">All Months</option>
                        @foreach($months as $monthValue => $monthName)
                            <option value="{{ $monthValue }}" {{ request('month') == $monthValue ? 'selected' : '' }}>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Filter --}}
                <div class="space-y-2">
                    <label for="action_filter" class="block text-sm font-medium text-gray-700">Action Type</label>
                    <select 
                        id="action_filter" 
                        name="action"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                    >
                        <option value="">All Actions</option>
                        <optgroup label="User Actions">
                            <option value="user_created" {{ request('action') == 'user_created' ? 'selected' : '' }}>User Created</option>
                            <option value="user_updated" {{ request('action') == 'user_updated' ? 'selected' : '' }}>User Updated</option>
                            <option value="user_deactivated" {{ request('action') == 'user_deactivated' ? 'selected' : '' }}>User Deactivated</option>
                            <option value="user_reactivated" {{ request('action') == 'user_reactivated' ? 'selected' : '' }}>User Reactivated</option>
                        </optgroup>

                        <optgroup label="Order Actions">
                            <option value="order_approved" {{ request('action') == 'order_approved' ? 'selected' : '' }}>Order Approved</option>
                            <option value="order_declined" {{ request('action') == 'order_declined' ? 'selected' : '' }}>Order Declined</option>
                            <option value="order_ready_for_pickup" {{ request('action') == 'order_ready_for_pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                            <option value="order_picked_up" {{ request('action') == 'order_picked_up' ? 'selected' : '' }}>Order Picked Up</option>
                            <option value="component_ready_for_pickup" {{ request('action') == 'component_ready_for_pickup' ? 'selected' : '' }}>Component Ready</option>
                            <option value="component_picked_up" {{ request('action') == 'component_picked_up' ? 'selected' : '' }}>Component Picked Up</option>
                            <option value="invoice_created" {{ request('action') == 'invoice_created' ? 'selected' : '' }}>Invoice Created</option>
                        </optgroup>

                        <optgroup label="Component Actions">
                            <option value="component_created" {{ request('action') == 'component_created' ? 'selected' : '' }}>Component Created</option>
                            <option value="component_updated" {{ request('action') == 'component_updated' ? 'selected' : '' }}>Component Updated</option>
                            <option value="component_deleted" {{ request('action') == 'component_deleted' ? 'selected' : '' }}>Component Deleted</option>
                            <option value="component_restored" {{ request('action') == 'component_restored' ? 'selected' : '' }}>Component Restored</option>
                            <option value="component_image_updated" {{ request('action') == 'component_image_updated' ? 'selected' : '' }}>Image Updated</option>
                            <option value="component_3d_model_updated" {{ request('action') == 'component_3d_model_updated' ? 'selected' : '' }}>3D Model Updated</option>
                            <option value="component_image_deleted" {{ request('action') == 'component_image_deleted' ? 'selected' : '' }}>Image Deleted</option>
                            <option value="component_3d_model_deleted" {{ request('action') == 'component_3d_model_deleted' ? 'selected' : '' }}>3D Model Deleted</option>
                        </optgroup>

                        <optgroup label="Stock Actions">
                            <option value="stock_in" {{ request('action') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out" {{ request('action') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="stock_out_failed" {{ request('action') == 'stock_out_failed' ? 'selected' : '' }}>Stock Out Failed</option>
                            <option value="stock_adjusted" {{ request('action') == 'stock_adjusted' ? 'selected' : '' }}>Stock Adjusted</option>
                            <option value="low_stock_warning" {{ request('action') == 'low_stock_warning' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('action') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </optgroup>
                    </select>
                </div>

                {{-- User Filter --}}
                <div class="space-y-2">
                    <label for="user_filter" class="block text-sm font-medium text-gray-700">User</label>
                    <select 
                        id="user_filter" 
                        name="user_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                    >
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quick Date Presets --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <div class="flex space-x-2">
                        <a href="?date_from={{ now()->subDays(7)->format('Y-m-d') }}&date_to={{ now()->format('Y-m-d') }}" 
                        class="flex-1 text-center px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Last 7 Days
                        </a>
                        <a href="?date_from={{ now()->subDays(30)->format('Y-m-d') }}&date_to={{ now()->format('Y-m-d') }}" 
                        class="flex-1 text-center px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Last 30 Days
                        </a>
                    </div>
                </div>
            </div>

            {{-- Custom Date Range --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-gray-200">
                <div class="space-y-2">
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input 
                        type="date" 
                        id="date_from" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                    >
                </div>
                <div class="space-y-2">
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input 
                        type="date" 
                        id="date_to" 
                        name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                    >
                </div>
                <div class="flex items-end space-x-3">
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm"
                    >
                        Apply Filters
                    </button>
                    <a 
                        href="{{ route('admin.activitylogs') }}" 
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors text-center"
                    >
                        Clear Filters
                    </a>
                </div>
            </div>

            {{-- Active Filters Badges --}}
            @if(request()->anyFilled(['month', 'action', 'user_id', 'date_from', 'date_to']))
            <div class="pt-3 border-t border-gray-200">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Active filters:</span>
                    @if(request('month'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                            Month: {{ \Illuminate\Support\Str::limit($months[request('month')] ?? request('month'), 15) }}
                            <a href="{{ request()->fullUrlWithoutQuery('month') }}" class="ml-1 hover:text-blue-900 text-sm">×</a>
                        </span>
                    @endif
                    @if(request('action'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                            Action: {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::headline(request('action')), 12) }}
                            <a href="{{ request()->fullUrlWithoutQuery('action') }}" class="ml-1 hover:text-green-900 text-sm">×</a>
                        </span>
                    @endif
                    @if(request('user_id'))
                        @php $selectedUser = $users->firstWhere('id', request('user_id')); @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 whitespace-nowrap">
                            User: {{ \Illuminate\Support\Str::limit($selectedUser->first_name ?? 'Unknown', 10) }}
                            <a href="{{ request()->fullUrlWithoutQuery('user_id') }}" class="ml-1 hover:text-purple-900 text-sm">×</a>
                        </span>
                    @endif
                    @if(request('date_from') || request('date_to'))
                        @php
                            $fromDate = request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d') : 'Start';
                            $toDate = request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d') : 'End';
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 whitespace-nowrap">
                            Date: {{ $fromDate }} - {{ $toDate }}
                            <a href="{{ request()->fullUrlWithoutQuery(['date_from', 'date_to']) }}" class="ml-1 hover:text-orange-900 text-sm">×</a>
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </form>
    </section>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[calc(100vh-250px)]">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <colgroup>
                    <col class="w-[20%]">  
                    <col class="w-[15%]">  
                    <col class="w-[50%]"> 
                    <col class="w-[15%]"> 
                </colgroup>
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                            <div class="text-sm text-gray-500">{{ $log->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $actionColors = [
                                    'user_created' => 'bg-blue-100 text-blue-800',
                                    'user_updated' => 'bg-indigo-100 text-indigo-800',
                                    'user_deactivated' => 'bg-red-100 text-red-800',
                                    'user_reactivated' => 'bg-green-100 text-green-800',
                                    'user_verification_approved' => 'bg-green-100 text-green-800',
                                    'user_verification_rejected' => 'bg-red-100 text-red-800',
                                    'user_verification_declined' => 'bg-orange-100 text-orange-800',
                                    'order_approved' => 'bg-green-100 text-green-800',
                                    'order_declined' => 'bg-red-100 text-red-800',
                                    'order_ready_for_pickup' => 'bg-blue-100 text-blue-800',
                                    'order_picked_up' => 'bg-green-100 text-green-800',
                                    'component_ready_for_pickup' => 'bg-blue-100 text-blue-800',
                                    'component_picked_up' => 'bg-green-100 text-green-800',
                                    'invoice_created' => 'bg-purple-100 text-purple-800',
                                    'component_created' => 'bg-green-100 text-green-800',
                                    'component_updated' => 'bg-blue-100 text-blue-800',
                                    'component_deleted' => 'bg-red-100 text-red-800',
                                    'component_restored' => 'bg-yellow-100 text-yellow-800',
                                    'stock_in' => 'bg-green-100 text-green-800',
                                    'stock_out' => 'bg-blue-100 text-blue-800',
                                    'stock_out_failed' => 'bg-red-100 text-red-800',
                                    'stock_adjusted' => 'bg-orange-100 text-orange-800',
                                    'low_stock_warning' => 'bg-yellow-100 text-yellow-800',
                                    'out_of_stock' => 'bg-red-100 text-red-800',
                                ];
                                
                                $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                {{ Illuminate\Support\Str::headline($log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $log->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center">
                            <div class="text-gray-500 text-sm">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="mt-2">No activity logs found for the selected filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table> 
        </div>
    </section>

    {{-- PAGINATION --}}
    <div class="mt-6 pb-6">
        {{ $logs->appends(request()->query())->links() }}
    </div>

</x-dashboardlayout>