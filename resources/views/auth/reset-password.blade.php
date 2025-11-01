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

            <!-- New Password Field with Feedback -->
            <div class="relative">
                <label for="password">New Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    oninput="validatePassword(this.value)"
                    class="pr-10"
                >
                
                <!-- Password Requirements -->
                <div id="passwordFeedback" class="mt-2 text-sm space-y-1 hidden">
                    <div id="length" class="flex items-center gap-2 text-gray-500">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center text-xs">•</span>
                        At least 8 characters
                    </div>
                    <div id="uppercase" class="flex items-center gap-2 text-gray-500">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center text-xs">•</span>
                        One uppercase letter
                    </div>
                    <div id="lowercase" class="flex items-center gap-2 text-gray-500">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center text-xs">•</span>
                        One lowercase letter
                    </div>
                    <div id="number" class="flex items-center gap-2 text-gray-500">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center text-xs">•</span>
                        One number
                    </div>
                    <div id="special" class="flex items-center gap-2 text-gray-500">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center text-xs">•</span>
                        One special character
                    </div>
                </div>
            </div>
            
            <!-- Confirm Password Field -->
            <div class="relative">
                <label for="password_confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation"
                    oninput="checkPasswordMatch()"
                    class="pr-10"
                >
                
                <!-- Password Match Indicator -->
                <div id="passwordMatch" class="mt-1 text-sm hidden">
                    <span class="flex items-center gap-2">
                        <span id="matchIcon" class="w-4 h-4 rounded-full border flex items-center justify-center text-xs">•</span>
                        <span id="matchText">Passwords match</span>
                    </span>
                </div>
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

    <script>
        function validatePassword(password) {
            const feedback = document.getElementById('passwordFeedback');
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };

            // Show/hide feedback container
            if (password.length > 0) {
                feedback.classList.remove('hidden');
            } else {
                feedback.classList.add('hidden');
            }

            // Update each requirement indicator
            Object.keys(requirements).forEach(key => {
                const element = document.getElementById(key);
                const indicator = element.querySelector('span');
                
                if (requirements[key]) {
                    indicator.classList.remove('border-gray-300');
                    indicator.classList.add('bg-green-500', 'border-green-500', 'text-white');
                    indicator.textContent = '✓';
                    element.classList.remove('text-gray-500');
                    element.classList.add('text-green-600');
                } else {
                    indicator.classList.remove('bg-green-500', 'border-green-500', 'text-white');
                    indicator.classList.add('border-gray-300');
                    indicator.textContent = '•';
                    element.classList.remove('text-green-600');
                    element.classList.add('text-gray-500');
                }
            });

            // Also check password match when password changes
            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const matchElement = document.getElementById('passwordMatch');
            const matchIcon = document.getElementById('matchIcon');
            const matchText = document.getElementById('matchText');

            if (confirmPassword.length === 0) {
                matchElement.classList.add('hidden');
                return;
            }

            matchElement.classList.remove('hidden');

            if (password === confirmPassword && password.length > 0) {
                matchIcon.classList.remove('border-gray-300', 'bg-red-500', 'border-red-500');
                matchIcon.classList.add('bg-green-500', 'border-green-500', 'text-white');
                matchIcon.textContent = '✓';
                matchText.textContent = 'Passwords match';
                matchText.classList.remove('text-red-600');
                matchText.classList.add('text-green-600');
            } else {
                matchIcon.classList.remove('bg-green-500', 'border-green-500', 'text-white');
                matchIcon.classList.add('bg-red-500', 'border-red-500', 'text-white');
                matchIcon.textContent = '✕';
                matchText.textContent = 'Passwords do not match';
                matchText.classList.remove('text-green-600');
                matchText.classList.add('text-red-600');
            }
        }

        // Show password requirements when password field is focused
        document.getElementById('password').addEventListener('focus', function() {
            if (this.value.length === 0) {
                document.getElementById('passwordFeedback').classList.remove('hidden');
            }
        });

        // Hide password requirements when clicking outside (optional)
        document.addEventListener('click', function(e) {
            const passwordField = document.getElementById('password');
            const feedback = document.getElementById('passwordFeedback');
            
            if (!passwordField.contains(e.target) && passwordField.value.length === 0) {
                feedback.classList.add('hidden');
            }
        });

        // Initialize validation if there's already a value (e.g., from browser autofill)
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            if (passwordField.value) {
                validatePassword(passwordField.value);
            }
            
            const confirmPasswordField = document.getElementById('password_confirmation');
            if (confirmPasswordField.value) {
                checkPasswordMatch();
            }
        });
    </script>
</body>
</html>