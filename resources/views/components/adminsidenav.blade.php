<nav class="nav-bar">

    {{-- fetching the user role in the database --}}
    {{-- ucfirst -> capitalizes the first character string --}}
    @if ($role === 'Customer')
        <h1>Techboxx</h1>
    @else
        <h1>{{ ucfirst($role) }}</h1>
    @endif

    {{-- grouping links in an array based on roles --}}
    @php
        $links = match($role) {
            'Admin' => [
                ['route' => route('admin.dashboard') , 'label' => 'Dashboard', 'icon' => 'dashboard'],
                ['route' => route('admin.useraccount') , 'label' => 'User Account', 'icon' => 'user'],
                ['route' => route('staff.order') , 'label' => 'Order', 'icon' => 'order'],
                ['route' => route('staff.supplier') , 'label' => 'Supplier', 'icon' => 'supplier'],
                ['route' => route('staff.componentdetails') , 'label' => 'Component Details', 'icon' => 'component'],
                ['route' => route('admin.sales') , 'label' => 'Sales Report', 'icon' => 'bargraph'],
                ['route' => route('staff.inventory') , 'label' => 'Inventory Report', 'icon' => 'inventory'],
                ['route' => route('staff.software-details') , 'label' => 'Software Details', 'icon' => 'software'],
                ['route' => '' , 'label' => 'Activity Logs', 'icon' => 'logs'],
                ['route' => route('techboxx.build') , 'label' => 'Build', 'icon' => 'build', 'style' => 'last-nav'],
            ],
            'Staff' => [
                ['route' => route('staff.dashboard') , 'label' => 'Dashboard', 'icon' => 'dashboard'],
                ['route' => route('staff.order') , 'label' => 'Order', 'icon' => 'order'],
                ['route' => route('staff.supplier') , 'label' => 'Supplier', 'icon' => 'supplier'],
                ['route' => route('staff.componentdetails') , 'label' => 'Component Details', 'icon' => 'component'],
                ['route' => route('staff.software-details') , 'label' => 'Software Details', 'icon' => 'software'],
                ['route' => route('staff.inventory') , 'label' => 'Inventory Report', 'icon' => 'inventory'],
                ['route' => route('techboxx.build') , 'label' => 'Build', 'icon' => 'build', 'style' => 'last-nav'],
            ],
            'Customer' => [
                ['route' => route('customer.dashboard'), 'label' => 'Profile', 'icon' => 'dashboard'],
                ['route' => '', 'label' => 'Checkout Details', 'icon' => 'checkout'],
                ['route' => '', 'label' => 'Order Details', 'icon' => 'order'],
                ['route' => '', 'label' => 'Purchased History', 'icon' => 'purchase'],
            ],
            default => []
        };
    @endphp

    {{-- rendering links base on roles --}}
    <ul>
        @foreach ($links as $link)
            @php
                // checking if the current url matches the route
                $isActive = request()->is(ltrim(parse_url($link['route'], PHP_URL_PATH), '/' . '*'))
            @endphp
            <li class="{{ (($link['style'] ?? '' ) . ($isActive ? ' active' : '')) }}">
                <a href="{{ $link['route'] }}">
                    <x-dynamic-component :component="'x-icons.' . $link['icon']" />
                    {{ $link['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

</nav>