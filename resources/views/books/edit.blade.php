@extends('layouts.app', ['title' => 'Ubah Buku Keuangan'])

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('books.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2">
            ← Kembali ke Daftar Buku
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ubah Informasi Buku</h1>
        <p class="text-sm text-slate-500 mt-1">Ubah nama atau deskripsi buku kas '{{ $book->name }}'.</p>
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

        <form action="{{ route('books.update', $book) }}" method="POST" data-prevent-duplicate class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">
                    Nama Buku Keuangan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $book->name) }}" required
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">
                    Deskripsi / Tujuan Buku
                </label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047] transition">{{ old('description', $book->description) }}</textarea>
            </div>

            <!-- Locked Opening Balance info -->
            <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl text-xs text-slate-600">
                <span class="font-bold block text-slate-700">Informasi Saldo Awal Terkunci:</span>
                <p class="mt-1">
                    Saldo awal buku kas adalah <strong>Rp{{ number_format($book->opening_balance, 2, ',', '.') }}</strong> per tanggal <strong>{{ \Carbon\Carbon::parse($book->opening_balance_date)->format('d F Y') }}</strong>. 
                    Nilai ini dikunci untuk menjaga integritas saldo berjalan.
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#43A047] hover:bg-[#388E3C] text-white font-bold text-sm shadow-sm transition cursor-pointer">
                    Perbarui Buku Kas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
