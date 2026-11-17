@extends('layouts.guest', ['title' => '404 — Halaman Tidak Ditemukan'])

@section('content')
<div class="text-center max-w-md bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
    <div class="w-16 h-16 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-black">
        404
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Halaman Tidak Ditemukan</h1>
    <p class="text-sm text-slate-600 mb-6">
        Halaman atau transaksi keuangan yang Anda cari tidak tersedia atau tautan sudah kedaluwarsa.
    </p>
    <div class="flex justify-center gap-3">
        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-[#43A047] hover:bg-green-700 text-white rounded-xl font-semibold text-sm transition">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
