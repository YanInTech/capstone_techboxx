

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password</title>

    @vite(['resources\css\app.css', 'resources\css\verificationform.css'])

</head>
<body>
    <form action="{{ route('password.email') }}" method="POST" class="form">
        @csrf
        <x-logoheader>
            <x-slot name="header">
                <h1>Forgot Your Password?</h1>
                <p>No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
            </x-slot>

            <div class="mt-4">
                <label for="email">Email</label>
                <input type="email" name="email">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs absolute bottom-[25%]" />
            </div>

            @if (session('status'))
                <p class="text-green-500 absolute bottom-[25%]">
                    {{ session('status') }}
                </p>
            @endif

        </x-logoheader>
        
        <x-usersbutton label="Email Password Reset Link"/>

    </form>
</body>
</html>