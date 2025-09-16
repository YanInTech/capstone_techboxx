
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Password</title>

    @vite(['resources\css\app.css', 'resources\css\verificationform.css'])

</head>
<body>
    <form action="{{ route('password.store') }}" method="POST" class="form">
        @csrf
        <x-logoheader>
            <x-slot name="header">
                <h1>Reset Your Password</h1>
                <p>What would you like your new password to be?</p>
            </x-slot>

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" :value="old('email', $request->email)">
            </div>

            <div>
                <label for="password">New Password</label>
                <input type="password" name="password">    
            </div>
            
            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation">  
            </div>
        </x-logoheader>

        {{-- validation errors --}}
        @if ($errors->any())
            <ul class=" text-left text-xs text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <x-usersbutton label="Reset Password"/>
    </form>
</body>
</html>