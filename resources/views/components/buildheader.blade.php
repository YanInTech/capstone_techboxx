<header class="header">
    <div class="header-logo">
        <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="logo">
        
        @auth
            @if (auth()->user()->role === 'Customer')
                <a href="{{ route('home') }}"><h2>Madoxx.qwe</h2></a>
            @else
                <h2>Madoxx.qwe</h2> {{-- No link for non-customers --}}
            @endif
        @else
            <h2>Madoxx.qwe</h2> {{-- No link if not logged in --}}
        @endauth
    </div>

    <div class="header-nav">
        <div class="header-link">
            @auth
                @if (auth()->user()->role === 'Customer')
                    <a href="">Your Builds</a>
                    <a href="/cart">Cart</a>
                    <a href="{{ route('catalogue') }}">Products</a>
                @endif
            @endauth
        </div>

        <div class="header-button">
            @auth
                @if (auth()->user()->role === 'Customer')
                    <form action="{{ route('customer.dashboard') }}">
                        @csrf
                        <button>{{ $name }}</button>
                    </form>
                @elseif (auth()->user()->role === 'Staff')
                    <form action="{{ route('staff.dashboard') }}">
                        @csrf
                        <button>{{ $name }}</button>
                    </form>
                @elseif (auth()->user()->role === 'Admin')
                    <form action="{{ route('admin.dashboard') }}">
                        @csrf
                        <button>{{ $name }}</button>
                    </form>
                @endif
            @else
                <form action="{{ route('login') }}">
                    @csrf
                    <button>Sign In</button>
                </form>
            @endauth
        </div>
    </div>
</header>
