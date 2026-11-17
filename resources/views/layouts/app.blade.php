<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Finora</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2343A047'><path d='M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z'/></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand Logo & Tagline -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-lg bg-[#43A047] flex items-center justify-center text-white font-bold shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-slate-900">Finora</span>
                            <span class="hidden md:inline-block ml-2 text-xs font-medium text-[#43A047] bg-green-50 px-2 py-0.5 rounded-full border border-green-200">Financial System</span>
                        </div>
                    </a>
                </div>

                <!-- Active Book Switcher & User Dropdown -->
                <div class="flex items-center space-x-4">
                    @auth
                        @php
                            $activeBook = session('active_book_id') 
                                ? Auth::user()->activeBooks()->find(session('active_book_id')) 
                                : Auth::user()->activeBooks()->first();
                            $userBooks = Auth::user()->activeBooks()->get();
                        @endphp

                        @if($userBooks->isNotEmpty())
                        <!-- Active Ledger Switcher -->
                        <div class="relative">
                            <form id="ledger-switch-form" action="{{ route('ledger.switch') }}" method="POST" class="flex items-center">
                                @csrf
                                <label for="active_book_id" class="hidden sm:inline-block text-xs font-semibold text-slate-500 mr-2">Buku Kas:</label>
                                <select id="active_book_id" name="book_id" onchange="document.getElementById('ledger-switch-form').submit()"
                                    class="text-sm font-semibold bg-green-50 border border-green-300 text-green-900 rounded-lg focus:ring-[#43A047] focus:border-[#43A047] py-1.5 px-3 pr-8 cursor-pointer shadow-xs">
                                    @foreach($userBooks as $b)
                                        <option value="{{ $b->id }}" {{ ($activeBook && $activeBook->id === $b->id) ? 'selected' : '' }}>
                                            📖 {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @endif

                        <!-- User Profile Pill -->
                        <div class="flex items-center space-x-3 pl-3 border-l border-slate-200">
                            <a href="{{ route('profile.show') }}" class="flex items-center space-x-2 text-slate-700 hover:text-[#43A047] transition">
                                <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center font-bold text-xs text-slate-700">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <span class="hidden lg:inline-block text-sm font-semibold">{{ Auth::user()->name }}</span>
                            </a>

                            <!-- Logout Form -->
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow flex w-full gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-64 shrink-0 hidden md:block">
            <nav class="bg-white rounded-xl border border-slate-200 shadow-xs p-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#43A047] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('books.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('books.*') ? 'bg-[#43A047] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Buku Keuangan</span>
                </a>

                <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kas & Transaksi</div>

                <a href="{{ route('transactions.income.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.income.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    <span>Pemasukan</span>
                </a>

                <a href="{{ route('transactions.expense.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.expense.*') ? 'bg-red-50 text-red-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <span>Pengeluaran</span>
                </a>

                <a href="{{ route('transactions.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.index') ? 'bg-slate-100 text-[#43A047] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Semua Transaksi</span>
                </a>

                <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Analisis</div>

                <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-[#43A047] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Laporan Keuangan</span>
                </a>

                @if(Auth::user()->isAdmin())
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-purple-600">Sistem Admin</div>

                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Manajemen User</span>
                </a>

                <a href="{{ route('admin.audit.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.audit.*') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Audit Trail</span>
                </a>

                <a href="{{ route('admin.logs.activity') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.logs.*') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Log Aktivitas & Error</span>
                </a>

                <a href="{{ route('admin.backup.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.backup.*') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    <span>Backup Database</span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content View -->
        <main class="flex-1 w-full min-w-0">
            <!-- Flash Message Alerts -->
            @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-[#43A047] p-4 rounded-r-lg shadow-xs flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-[#43A047]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-semibold text-green-900">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-xs flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-semibold text-red-900">{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Global Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-5 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <span class="font-bold text-[#43A047]">Finora</span> — Financial Management System. "Kelola Keuangan dengan Mudah, Aman, dan Teratur."
        </div>
    </footer>
</body>
</html>
