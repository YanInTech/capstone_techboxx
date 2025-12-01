<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>

    @vite(['resources\css\app.css', 'resources\css\verificationform.css'])
</head>
<body>
    @if (session('status'))
        <div class="flash !top-0">
            {{ session('status') }}
        </div>
    @endif
        
    <form action="{{ route('login') }}" method="POST" class="form">
        @csrf
        
        <x-logoheader>
            <x-slot name="header">
                <h1>Login</h1>
            </x-slot>

            <div>
                <label for="email">Email</label>
                <input type="email" name="email">    
            </div>
            
            <div>
                <label for="password">Password</label>
                <input type="password" name="password">   
                <div class="flex justify-between">
                    <a href="{{ route('register') }}" class="forget-pass">Sign up</a>
                    <a href="{{ route('password.request') }}" class="forget-pass">Forgot Password</a>     
                </div>
                
            </div>
        </x-logoheader>

        {{-- validation errors --}}
        @if ($errors->any())
            <ul class="absolute top-[69%] text-left text-xs text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <x-usersbutton label="Login"/>
        
    </form>
    
</body>
</html>