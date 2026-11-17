@extends('layouts.app', ['title' => 'Kelola Anggota — ' . $book->name])

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div>
        <a href="{{ route('books.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2">
            ← Kembali ke Daftar Buku
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Anggota Buku: {{ $book->name }}</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola kolaborasi pengguna yang berhak mencatat dan melihat transaksi pada buku ini.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-green-100 text-green-800 border border-green-200">
                Peran Anda: {{ strtoupper($currentUserRole) }}
            </span>
        </div>
    </div>

    @can('manageMembers', $book)
    <!-- Add Member Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span>➕</span>
            <span>Undang / Tambah Anggota Baru</span>
        </h2>
        <form action="{{ route('books.members.store', $book) }}" method="POST" data-prevent-duplicate class="flex flex-col sm:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1 w-full">
                <label for="identifier" class="block text-xs font-semibold text-slate-600 mb-1">
                    Username atau Email Pengguna <span class="text-red-500">*</span>
                </label>
                <input type="text" id="identifier" name="identifier" required placeholder="Contoh: opasantoso atau budi@finora.local"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047]">
            </div>

            <div class="w-full sm:w-48">
                <label for="role" class="block text-xs font-semibold text-slate-600 mb-1">
                    Peran Buku <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-[#43A047] focus:border-[#43A047]">
                    <option value="member">Anggota (Catat & Lihat)</option>
                    <option value="admin">Admin Buku (Kelola Kas & Anggota)</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-[#43A047] hover:bg-[#388E3C] text-white font-bold text-sm rounded-xl shadow-xs transition cursor-pointer shrink-0">
                Tambah Anggota
            </button>
        </form>
    </div>
    @endcan

    <!-- Members List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800">Daftar Anggota Aktif ({{ $members->total() }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-6">Pengguna</th>
                        <th class="py-3 px-6">Username / Email</th>
                        <th class="py-3 px-6">Peran di Buku</th>
                        <th class="py-3 px-6">Bergabung</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @foreach($members as $m)
                    @php
                        $role = $m->pivot->role;
                        $isOwner = $role === 'owner';
                        $isSelf = (int) $m->id === (int) Auth::id();
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 font-semibold text-slate-900 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                {{ strtoupper(substr($m->name, 0, 2)) }}
                            </div>
                            <div>
                                <span>{{ $m->name }}</span>
                                @if($isSelf)
                                <span class="text-xs text-green-600 font-bold ml-1">(Anda)</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-500 font-mono">
                            {{ $m->username }}<br>
                            <span class="text-slate-400 font-sans">{{ $m->email ?: '-' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $isOwner ? 'bg-amber-100 text-amber-800' : ($role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $isOwner ? '👑 Pemilik' : ($role === 'admin' ? '🛡️ Admin Buku' : '👤 Anggota') }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($m->pivot->joined_at)->format('d M Y') }}
                        </td>
                        <td class="py-4 px-6 text-right">
                            @if(!$isOwner && Auth::user()->can('manageMembers', $book))
                            <div class="inline-flex items-center space-x-2">
                                <!-- Ubah Role -->
                                <form action="{{ route('books.members.update', [$book, $m]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="role" value="{{ $role === 'admin' ? 'member' : 'admin' }}">
                                    <button type="submit" title="Ganti peran ke {{ $role === 'admin' ? 'Member' : 'Admin' }}" class="text-xs text-slate-600 hover:text-slate-900 font-semibold bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded-md transition cursor-pointer">
                                        {{ $role === 'admin' ? 'Jadikan Member' : 'Jadikan Admin' }}
                                    </button>
                                </form>

                                <!-- Hapus Anggota -->
                                <form action="{{ route('books.members.destroy', [$book, $m]) }}" method="POST" class="inline" onsubmit="return confirm('Keluarkan anggota {{ $m->name }} dari buku ini? Riwayat transaksi miliknya akan tetap tersimpan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Keluarkan Anggota" class="text-xs text-red-600 hover:text-red-800 font-semibold bg-red-50 hover:bg-red-100 px-2 py-1 rounded-md transition cursor-pointer">
                                        Keluarkan
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-slate-400 italic">{{ $isOwner ? 'Pemilik Utama' : '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $members->links() }}
        </div>
    </div>

    @can('transferOwnership', $book)
    <!-- Transfer Ownership Section -->
    <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-6">
        <h2 class="text-base font-bold text-amber-900 mb-2 flex items-center gap-2">
            <span>👑</span>
            <span>Alihkan Kepemilikan Buku Kas (Transfer Ownership)</span>
        </h2>
        <p class="text-xs text-amber-800 mb-4 leading-relaxed">
            Sebagai Pemilik Utama, Anda dapat memindahkan kepemilikan buku ini ke anggota lain. Setelah dipindahkan, peran Anda akan otomatis berubah menjadi Admin Buku.
        </p>
        <form action="{{ route('books.members.transfer', $book) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memindahkan kepemilikan buku ini? Tindakan ini tidak dapat dibatalkan secara sepihak.')" class="flex flex-col sm:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1 w-full">
                <label for="new_owner_id" class="block text-xs font-semibold text-amber-900 mb-1">
                    Pilih Calon Pemilik Baru:
                </label>
                <select id="new_owner_id" name="new_owner_id" required class="w-full px-3.5 py-2.5 bg-white border border-amber-300 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Pilih Anggota --</option>
                    @foreach($members as $m)
                        @if((int) $m->id !== (int) Auth::id())
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->username }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow-xs transition cursor-pointer shrink-0">
                Alihkan Kepemilikan
            </button>
        </form>
    </div>
    @endcan
</div>
@endsection
