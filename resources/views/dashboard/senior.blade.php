@extends('layouts.senior', ['title' => 'Dashboard Lansia'])

@section('content')
<div class="space-y-8">
    <!-- Welcome Card Lansia -->
    <div class="bg-green-50 border-4 border-[#43A047] p-8 rounded-3xl shadow-sm">
        <h1 class="text-4xl font-black text-slate-900">Halo, {{ Auth::user()->name }}!</h1>
        <p class="text-2xl font-semibold text-slate-700 mt-2">
            Buku Kas yang sedang dibuka: 
            <span class="font-black text-[#43A047] underline">
                {{ session('active_book_id') ? \App\Models\Book::find(session('active_book_id'))?->name : 'Buku Tabungan Pensiun' }}
            </span>
        </p>

        <!-- TTS Button Besar -->
        <div class="mt-6">
            <button type="button" onclick="finoraSpeak('Halo {{ Auth::user()->name }}. Saldo Anda saat ini adalah dua puluh tujuh juta tujuh ratus lima puluh ribu rupiah. Anda memiliki pemasukan tiga juta dua ratus ribu rupiah dan pengeluaran empat ratus lima puluh ribu rupiah.')"
                class="bg-[#43A047] hover:bg-green-700 text-white text-2xl font-extrabold px-8 py-5 rounded-2xl shadow-lg border-2 border-green-800 flex items-center space-x-3 cursor-pointer min-h-[68px]">
                <span class="text-3xl">🔊</span>
                <span>Bacakan Saldo Saya</span>
            </button>
        </div>
    </div>

    <!-- Saldo Utama Lansia (Ukuran 52px sesuai standar master prompt) -->
    <div class="bg-white border-4 border-slate-300 p-8 rounded-3xl shadow-md text-center">
        <span class="text-2xl font-bold uppercase tracking-wider text-slate-600 block mb-2">Total Saldo Kas Anda</span>
        <div class="text-[52px] font-black text-slate-900 tracking-tight leading-none py-2">
            Rp27.750.000
        </div>
        <p class="text-xl font-bold text-green-700 mt-3">✅ Kondisi Keuangan Aman dan Teratur</p>
    </div>

    <!-- Tombol Tindakan Cepat Lansia -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('transactions.income.create') }}" class="bg-green-100 hover:bg-green-200 border-4 border-green-600 p-6 rounded-3xl flex items-center justify-between text-green-950 transition min-h-[90px]">
            <div>
                <span class="text-3xl font-black block">➕ Tambah Uang Masuk</span>
                <span class="text-xl font-semibold text-green-800">Catat pensiun, hadiah, atau kiriman</span>
            </div>
            <span class="text-4xl">➔</span>
        </a>
        <a href="{{ route('transactions.expense.create') }}" class="bg-red-100 hover:bg-red-200 border-4 border-red-600 p-6 rounded-3xl flex items-center justify-between text-red-950 transition min-h-[90px]">
            <div>
                <span class="text-3xl font-black block">➖ Tambah Uang Keluar</span>
                <span class="text-xl font-semibold text-red-800">Catat belanja, obat, atau listrik</span>
            </div>
            <span class="text-4xl">➔</span>
        </a>
    </div>
</div>
@endsection
