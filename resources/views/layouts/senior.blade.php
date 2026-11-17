<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard Lansia' }} — Finora</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2343A047'><path d='M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z'/></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="senior-app-container" class="bg-white text-slate-900 antialiased min-h-screen flex flex-col font-sans elderly-root">

    <!-- Header & Accessibility Bar Khusus Orang Tua -->
    <header class="bg-slate-100 border-b-4 border-[#43A047] sticky top-0 z-30 shadow-md">
        <!-- Accessibility Toolbar -->
        <div class="bg-slate-800 text-white px-4 py-2 flex flex-wrap justify-between items-center text-sm gap-2">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-base">Alat Bantu Penglihatan:</span>
                <button type="button" onclick="setSeniorFontSize('normal')" class="bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded-lg text-base font-bold min-h-[44px] min-w-[44px] border border-slate-500 cursor-pointer">A-</button>
                <button type="button" onclick="setSeniorFontSize('large')" class="bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded-lg text-lg font-bold min-h-[44px] min-w-[44px] border border-slate-500 cursor-pointer">A</button>
                <button type="button" onclick="setSeniorFontSize('xlarge')" class="bg-[#43A047] hover:bg-green-600 px-3 py-1.5 rounded-lg text-xl font-black min-h-[44px] min-w-[44px] border border-white cursor-pointer">A+</button>
                <button type="button" onclick="toggleSeniorHighContrast()" class="ml-2 bg-yellow-400 hover:bg-yellow-300 text-black px-3 py-1.5 rounded-lg text-sm font-black min-h-[44px] border border-black cursor-pointer">
                    👁️ Kontras Tinggi
                </button>
            </div>

            <!-- Tombol Bantuan & Logout -->
            <div class="flex items-center space-x-4">
                <span class="text-base font-semibold text-slate-200">Pengguna: {{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg text-base font-bold min-h-[44px] border border-red-800 cursor-pointer">
                        🚪 Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Senior Navbar -->
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap justify-between items-center gap-4">
            <!-- Brand -->
            <a href="{{ route('dashboard.senior') }}" class="flex items-center space-x-3">
                <div class="w-14 h-14 rounded-2xl bg-[#43A047] flex items-center justify-center text-white font-extrabold text-2xl shadow-md border-2 border-green-700">
                    F
                </div>
                <div>
                    <span class="text-3xl font-black tracking-tight text-slate-900">Finora</span>
                    <p class="text-lg font-semibold text-[#43A047]">Aplikasi Keuangan Ramah Lansia</p>
                </div>
            </a>

            <!-- Active Book Switcher for Senior -->
            @php
                $activeBook = session('active_book_id') 
                    ? \App\Models\Book::find(session('active_book_id')) 
                    : Auth::user()->activeBooks()->first();
                $userBooks = Auth::user()->activeBooks()->get();
            @endphp
            @if($userBooks->isNotEmpty())
            <form id="senior-ledger-switch" action="{{ route('ledger.switch') }}" method="POST" class="flex items-center gap-2 bg-white p-2 rounded-xl border-2 border-slate-300 shadow-xs">
                @csrf
                <span class="text-xl font-bold text-slate-700">📖 Buku:</span>
                <select name="book_id" onchange="document.getElementById('senior-ledger-switch').submit()"
                    class="text-xl font-bold bg-green-50 border-2 border-[#43A047] text-green-900 rounded-lg p-2.5 cursor-pointer min-h-[56px]">
                    @foreach($userBooks as $b)
                        <option value="{{ $b->id }}" {{ ($activeBook && $activeBook->id === $b->id) ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        <!-- Tombol Navigasi Utama Khusus Lansia (Ukuran Besar & Jelas) -->
        <nav class="max-w-6xl mx-auto px-4 pb-3 flex flex-wrap gap-3">
            <a href="{{ route('dashboard.senior') }}" class="flex items-center space-x-2 px-5 py-3 rounded-xl text-xl font-bold border-2 transition min-h-[60px] {{ request()->routeIs('dashboard.senior') ? 'bg-[#43A047] text-white border-green-700 shadow-md' : 'bg-white text-slate-800 border-slate-300 hover:bg-slate-200' }}">
                <span>🏠 Beranda</span>
            </a>
            <a href="{{ route('transactions.income.create') }}" class="flex items-center space-x-2 px-5 py-3 rounded-xl text-xl font-bold bg-green-100 hover:bg-green-200 text-green-950 border-2 border-green-600 transition min-h-[60px]">
                <span class="text-2xl">➕</span>
                <span>Tambah Uang Masuk</span>
            </a>
            <a href="{{ route('transactions.expense.create') }}" class="flex items-center space-x-2 px-5 py-3 rounded-xl text-xl font-bold bg-red-100 hover:bg-red-200 text-red-950 border-2 border-red-600 transition min-h-[60px]">
                <span class="text-2xl">➖</span>
                <span>Tambah Uang Keluar</span>
            </a>
            <a href="{{ route('reports.index') }}" class="flex items-center space-x-2 px-5 py-3 rounded-xl text-xl font-bold bg-white text-slate-800 border-2 border-slate-300 hover:bg-slate-200 transition min-h-[60px]">
                <span>📊 Laporan Saya</span>
            </a>
        </nav>
    </header>

    <!-- Content Area Lansia -->
    <main class="max-w-6xl mx-auto px-4 py-8 flex-grow w-full">
        <!-- Flash Messages Besar & Kontras -->
        @if(session('success'))
        <div class="mb-6 bg-green-100 border-4 border-[#43A047] p-6 rounded-2xl shadow-md flex items-center space-x-4">
            <span class="text-4xl">✅</span>
            <span class="text-2xl font-black text-green-950">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-100 border-4 border-red-600 p-6 rounded-2xl shadow-md flex items-center space-x-4">
            <span class="text-4xl">⚠️</span>
            <span class="text-2xl font-black text-red-950">{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Khusus Lansia -->
    <footer class="bg-slate-100 border-t-2 border-slate-300 py-8 text-center text-lg font-bold text-slate-700 mt-auto">
        <p>&copy; {{ date('Y') }} Finora — Kelola Keuangan dengan Mudah, Aman, dan Teratur.</p>
        <p class="text-base text-slate-500 mt-1">Tekan tombol Bantuan jika Anda membutuhkan panduan penggunaan.</p>
    </footer>

</body>
</html>
