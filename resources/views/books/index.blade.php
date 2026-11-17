@extends('layouts.app', ['title' => 'Daftar Buku Keuangan'])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buku Keuangan Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola kas pribadi, keluarga, atau usaha dalam buku kas yang terpisah secara aman.</p>
        </div>
        <div>
            <a href="{{ route('books.create') }}" class="inline-flex items-center space-x-2 bg-[#43A047] hover:bg-[#388E3C] text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-xs transition">
                <span>➕</span>
                <span>Buat Buku Kas Baru</span>
            </a>
        </div>
    </div>

    <!-- Book Grid / List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($books as $book)
        @php
            $isActive = (int) $activeBookId === (int) $book->id;
            $userRole = $book->pivot->role ?? 'member';
        @endphp
        <div class="bg-white rounded-2xl border {{ $isActive ? 'border-2 border-[#43A047] shadow-md ring-2 ring-green-100' : 'border-slate-200 shadow-xs' }} p-6 flex flex-col justify-between transition hover:shadow-md">
            <div>
                <!-- Top Status Badge -->
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $userRole === 'owner' ? 'bg-amber-100 text-amber-800' : ($userRole === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                        Peran: {{ ucfirst($userRole) }}
                    </span>
                    @if($isActive)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#43A047] text-white">
                        ✓ BUKU AKTIF
                    </span>
                    @endif
                </div>

                <!-- Book Name & Description -->
                <h2 class="text-xl font-black text-slate-900 leading-snug">{{ $book->name }}</h2>
                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $book->description ?: 'Tidak ada deskripsi.' }}</p>

                <!-- Financial Balance Overview -->
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Saldo Awal ({{ \Carbon\Carbon::parse($book->opening_balance_date)->format('d/m/Y') }}):</span>
                        <span class="font-semibold text-slate-700">Rp{{ number_format($book->opening_balance, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-700">Saldo Kas Sekarang:</span>
                        <span class="text-base font-black {{ $book->current_balance >= 0 ? 'text-[#43A047]' : 'text-red-600' }}">
                            Rp{{ number_format($book->current_balance, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Jumlah Anggota:</span>
                        <span class="font-semibold text-slate-700">👥 {{ $book->members_count }} Pengguna</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                @if(!$isActive)
                <form action="{{ route('ledger.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <button type="submit" class="text-xs font-bold text-[#43A047] hover:text-green-800 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg border border-green-200 transition cursor-pointer">
                        Jadikan Aktif
                    </button>
                </form>
                @else
                <span class="text-xs font-bold text-[#43A047] italic">Sedang Dibuka</span>
                @endif

                <div class="flex items-center space-x-2">
                    <a href="{{ route('books.members', $book) }}" title="Kelola Anggota" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg text-xs font-semibold flex items-center gap-1 transition">
                        <span>👥 Anggota</span>
                    </a>

                    @can('update', $book)
                    <a href="{{ route('books.edit', $book) }}" title="Ubah Info" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg text-xs font-semibold flex items-center gap-1 transition">
                        <span>✏️ Ubah</span>
                    </a>
                    @endcan

                    @can('delete', $book)
                    <form action="{{ route('books.destroy', $book) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan buku kas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Arsipkan Buku" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg text-xs font-semibold transition cursor-pointer">
                            🗑️
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-12 rounded-2xl border border-slate-200 text-center">
            <span class="text-4xl block mb-2">📖</span>
            <h3 class="text-lg font-bold text-slate-800">Belum Ada Buku Keuangan</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Anda belum memiliki buku keuangan aktif. Silakan buat buku kas pertama Anda untuk mulai mengelola keuangan.</p>
            <div class="mt-5">
                <a href="{{ route('books.create') }}" class="bg-[#43A047] hover:bg-[#388E3C] text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition">
                    + Buat Buku Kas Pertama
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $books->links() }}
    </div>
</div>
@endsection
