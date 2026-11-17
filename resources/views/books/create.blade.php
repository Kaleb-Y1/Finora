@extends('layouts.app', ['title' => 'Buat Buku Keuangan Baru'])

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('books.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2">
            ← Kembali ke Daftar Buku
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Buku Keuangan Baru</h1>
        <p class="text-sm text-slate-500 mt-1">Buku keuangan dapat digunakan untuk memisahkan pencatatan kas pribadi, keluarga, usaha, atau organisasi.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ route('books.store') }}" method="POST" data-prevent-duplicate class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">
                    Nama Buku Keuangan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Contoh: Keuangan Usaha Toko, Kas RT 05, atau Tabungan Pribadi"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">
                    Deskripsi / Tujuan Buku (Opsional)
                </label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Tuliskan keterangan mengenai tujuan dan alur buku kas ini..."
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">{{ old('description') }}</textarea>
            </div>

            <!-- Grid: Opening Balance & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="opening_balance" class="block text-sm font-semibold text-slate-700 mb-1">
                        Saldo Awal (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" step="0.01" min="0" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', '0.00') }}" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Saldo kas saat pertama kali pencatatan dimulai.</span>
                </div>

                <div>
                    <label for="opening_balance_date" class="block text-sm font-semibold text-slate-700 mb-1">
                        Tanggal Saldo Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="opening_balance_date" name="opening_balance_date" value="{{ old('opening_balance_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Tanggal patokan saldo awal buku kas.</span>
                </div>
            </div>

            <!-- Notice Box -->
            <div class="bg-green-50 border border-green-200 p-4 rounded-xl text-xs text-green-900 flex items-start space-x-2.5">
                <span class="text-base">💡</span>
                <div>
                    <p class="font-bold">Kategori Standar Otomatis:</p>
                    <p class="mt-0.5">Finora akan secara otomatis menambahkan 10 kategori pemasukan & pengeluaran standar (Gaji, Makanan, Listrik, Transport, Kesehatan, dll.) sehingga Anda bisa langsung mencatat transaksi.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#43A047] hover:bg-[#388E3C] text-white font-bold text-sm shadow-sm transition cursor-pointer">
                    Simpan Buku Kas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
