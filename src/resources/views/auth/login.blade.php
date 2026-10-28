<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('branding/logo.svg') }}">
    <title>Login</title>
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 overflow-hidden">

    <!-- Background Blur -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-300/20 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-300/20 rounded-full blur-3xl -z-10"></div>

    <div class="min-h-screen flex">

        <!-- LEFT -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-8 lg:px-20">

            <div class="w-full max-w-md">

                <!-- Header -->
                <div class="mb-10">
                    <img src="/branding/logo-tag.svg" alt="Helpdesk" class="h-10 w-auto" />

                    <p class="text-slate-500 mt-2">
                        Sign in to continue your dashboard access
                    </p>
                </div>

                <!-- Card -->
                <div class="bg-white/70 backdrop-blur-2xl border border-white/50 shadow-2xl rounded-3xl p-8">

                    <!-- Error -->
                    @if ($errors->any())
                        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Email
                            </label>

                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="Enter your email"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white/80 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Password
                            </label>

                            <div class="relative">

                                <input id="password" type="password" name="password" required
                                    placeholder="Enter your password"
                                    class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 bg-white/80 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">

                                <!-- Toggle -->
                                <button type="button" id="togglePassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 hover:text-slate-700">

                                    <!-- Eye -->
                                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                               c4.477 0 8.268 2.943 9.542 7
                                               -1.274 4.057-5.065 7-9.542 7
                                               -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <!-- Eye Slash -->
                                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19
                                               c-4.478 0-8.268-2.943-9.542-7
                                               a9.956 9.956 0 012.223-3.592M6.228 6.228
                                               A9.953 9.953 0 0112 5c4.478 0 8.268 2.943
                                               9.542 7a9.97 9.97 0 01-4.293 5.568M6.228 6.228
                                               L3 3m3.228 3.228l3.65 3.65m0 0a3 3 0 104.243 4.243
                                               m-4.243-4.243L9.88 9.88m4.242 4.242L21 21" />
                                    </svg>

                                </button>

                            </div>
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between text-sm">

                            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">

                                <input type="checkbox" name="remember"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    {{ old('remember') ? 'checked' : '' }}>

                                Remember me
                            </label>

                            <a href="{{ route('filament.pemohon.auth.password-reset.request') }}"
                                class="text-blue-600 hover:text-blue-700 font-medium">
                                Forgot password?
                            </a>

                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow-lg hover:scale-[1.01] hover:shadow-xl transition duration-200">

                            Sign In

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="hidden lg:block lg:w-1/2 p-6">

            <div class="h-[calc(100vh-3rem)] rounded-l-[40px] overflow-hidden shadow-2xl">

                <img src="{{ asset('images/kampus.png') }}" alt="Campus" class="w-full h-full object-cover">

            </div>

        </div>

    </div>

    <!-- Password Toggle Script -->
    <script>
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        togglePassword.addEventListener('click', () => {

            const isPassword = password.type === 'password';

            password.type = isPassword ? 'text' : 'password';

            eyeOpen.classList.toggle('hidden');
            eyeClosed.classList.toggle('hidden');

        });
    </script>

</body>

</html>
