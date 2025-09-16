<x-dashboardlayout>
    <h2>Order Processing</h2>

    <div class="header-container">
        <div class="order-tab">
            <button class="active">Order Builds</button>
            <button>Check-out Components</button>
        </div>
    </div>

    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showModal: false, selectedBuild:{} }" 
            class="h-[55vh]">
            <table class="table mb-3">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Build Details</th>
                        <th>Order Date</th>
                        <th>Order Status</th>
                        <th>Pickup Status</th>
                        <th>Pickup Date</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr 
                        @class([
                            'bg-gray-200 text-gray-500 pointer-events-none' => $order->status === 'Declined',
                        ])
                    >
                        <td>{{ $order->id}}</td>
                        <td @click="showModal = true; selectedBuild = {{ $order->toJson() }};"
                            class="build-details">{{ $order->userBuild->build_name}}</td>
                        <td class="text-center !pr-[1.5%]">{{ $order->created_at ? $order->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->pickup_status }}</td>
                        <td>{{ $order->pickup_date ? $order->pickup_date->format('Y-m-d') : '-' }}</td>
                        <td>{{ $order->payment_status }}</td>
                        <td>{{ $order->payment_method }}</td>
                        <td class="align-middle text-center">
                            <div class="flex justify-center gap-2">
                                @if ($order->status === 'Pending')
                                    <form action={{ route('staff.order.approve', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit">
                                            <x-icons.check/>
                                        </button>
                                    </form>
                                    <form action={{ route('staff.order.decline', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit">
                                            <x-icons.close/>      
                                        </button>
                                    </form>
                                @elseif ($order->pickup_status === 'Picked up')
                                    {{-- NO ACTION --}}
                                @else
                                    <form action={{ route('staff.order.pickup', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit">
                                            <x-icons.pickup/>    
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>    
                    @endforeach
                </tbody>
            </table>

            {{-- VIEW MODAL --}}
            <div x-show="showModal" x-cloak x-transition class="modal overflow-y-scroll m-5">
                <div class="add-component" @click.away="showModal = false">
                    <h2>Build Details</h2>
                    <pre x-text="JSON.stringify(selectedBuild, null, 2)"></pre>
                    <div>
                        <div>
                            <p>Name</p>
                            <p x-text="selectedBuild.user.first_name + ' ' + selectedBuild.user.last_name"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{ $orders->links() }}

    </section>

</x-dashboardlayout>
