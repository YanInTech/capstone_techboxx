<header class="header">
    <div class="header-logo">
        <img src="{{ asset('images\Logo.png') }}" alt="Logo" class="logo">
        <a href="{{ route('home') }}"><h2>Madoxx.qwe</h2></a>  
    </div>
    <div class="header-nav">
        <div class="header-link">
            <a href="{{ route('customer.dashboard')}}">Your Builds</a>
            <a href="/cart">Cart</a>
            <a href="{{ route('catalogue') }}">Products</a>
        </div>
        <div class="header-button">
            @auth
                @if (auth()->user()->role === 'Customer')
                    {{-- show custom content for logged-in customer --}}
                    <form action="{{ route('customer.dashboard') }}">
                        @csrf
                        <button>{{ $name }}</button>
                    </form>
                @endif
            @else
                {{-- show sign in button if not authenticated --}}
                <form action="{{ route('login') }}">
                    @csrf
                    <button>Sign In</button>
                </form>
            @endauth

            <form action="{{ route('techboxx.build')}}">
                <button>Try the 3d PC Builder</button>
            </form>
        </div>
    </div>
</header>