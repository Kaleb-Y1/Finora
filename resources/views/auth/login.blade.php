@extends('layouts.guest', ['title' => 'Masuk ke Finora'])

@section('content')
<div class="w-full max-w-md">
    <!-- Brand Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#43A047] text-white shadow-md mb-3">
            <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Finora</h1>
        <p class="text-sm font-medium text-slate-600 mt-1">"Kelola Keuangan dengan Mudah, Aman, dan Teratur."</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-slate-800 mb-6">Masuk ke Akun Anda</h2>

        <!-- Flash Notification -->
        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center space-x-2">
            <svg class="w-5 h-5 text-[#43A047] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl flex items-start space-x-2">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div class="space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" data-prevent-duplicate class="space-y-5">
            @csrf

            <!-- Username or Email Field -->
            <div>
                <label for="login" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Username atau Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        placeholder="Contoh: budi atau budi@finora.local"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
                </div>
            </div>

            <!-- Password Field with Show/Hide Toggle -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-12 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
                    <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span id="eyeIconOpen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </span>
                        <span id="eyeIconClosed" class="hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="w-4 h-4 text-[#43A047] border-slate-300 rounded-sm focus:ring-[#43A047]">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-[#43A047] hover:bg-[#388E3C] text-white font-bold text-sm shadow-md hover:shadow-lg transition cursor-pointer flex items-center justify-center">
                Masuk ke Finora
            </button>
        </form>

        <!-- Quick Demo Account Pills -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5 text-center">Akun Percobaan (Password: password123)</p>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <button type="button" onclick="fillAccount('admin')" class="py-2 px-1 text-center bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg border border-purple-200 font-medium transition cursor-pointer">
                    👑 Admin<br><span class="text-[10px] text-purple-500 font-mono">admin</span>
                </button>
                <button type="button" onclick="fillAccount('budi')" class="py-2 px-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg border border-blue-200 font-medium transition cursor-pointer">
                    👤 Umum<br><span class="text-[10px] text-blue-500 font-mono">budi</span>
                </button>
                <button type="button" onclick="fillAccount('opasantoso')" class="py-2 px-1 text-center bg-green-50 hover:bg-green-100 text-green-700 rounded-lg border border-green-200 font-medium transition cursor-pointer">
                    👴 Orang Tua<br><span class="text-[10px] text-green-600 font-mono">opasantoso</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeIconOpen');
    const eyeClosed = document.getElementById('eyeIconClosed');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

function fillAccount(username) {
    document.getElementById('login').value = username;
    document.getElementById('password').value = 'password123';
}
</script>
@endsection
