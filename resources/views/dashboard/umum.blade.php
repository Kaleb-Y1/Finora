@extends('layouts.app', ['title' => 'Dashboard Umum'])

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-[#43A047] text-white p-6 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold">Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p class="text-green-100 text-sm mt-1">
                Role Anda: <span class="uppercase font-semibold tracking-wider bg-white/20 px-2 py-0.5 rounded-md text-xs">{{ Auth::user()->role }}</span> | 
                Buku Kas Aktif: <span class="font-bold underline">{{ session('active_book_id') ? \App\Models\Book::find(session('active_book_id'))?->name : 'Belum dipilih' }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('transactions.income.create') }}" class="bg-white text-green-800 hover:bg-green-50 px-4 py-2 rounded-xl text-sm font-bold shadow-xs transition">
                + Catat Pemasukan
            </a>
            <a href="{{ route('transactions.expense.create') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-xs transition">
                - Catat Pengeluaran
            </a>
        </div>
    </div>

    <!-- Summary Metrics Grid Preview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Saldo Kas</span>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp{{ number_format(16300000, 2, ',', '.') }}</p>
            <span class="text-xs text-green-600 font-semibold mt-1 inline-block">↑ Buku Kas Aktif</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pemasukan Bulan Ini</span>
            <p class="text-2xl font-black text-green-700 mt-1">Rp{{ number_format(7500000, 2, ',', '.') }}</p>
            <span class="text-xs text-slate-500 mt-1 inline-block">1 Transaksi</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengeluaran Bulan Ini</span>
            <p class="text-2xl font-black text-red-600 mt-1">Rp{{ number_format(1200000, 2, ',', '.') }}</p>
            <span class="text-xs text-slate-500 mt-1 inline-block">2 Transaksi</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Transaksi</span>
            <p class="text-2xl font-black text-slate-900 mt-1">3</p>
            <span class="text-xs text-slate-500 mt-1 inline-block">Tercatat di sistem</span>
        </div>
    </div>
</div>
@endsection
