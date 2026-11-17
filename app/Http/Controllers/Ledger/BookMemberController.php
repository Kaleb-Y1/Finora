<?php

namespace App\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ledger\AddMemberRequest;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookMemberController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display members of a specific book.
     */
    public function index(Book $book): View
    {
        $this->authorize('view', $book);

        $members = $book->members()
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->orderByRaw("FIELD(book_user.role, 'owner', 'admin', 'member')")
            ->paginate(15);

        $currentUserRole = $book->members()->where('users.id', Auth::id())->first()?->pivot->role;

        return view('books.members', compact('book', 'members', 'currentUserRole'));
    }

    /**
     * Add / invite a member to the book.
     */
    public function store(AddMemberRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('manageMembers', $book);

        $identifier = trim($request->identifier);

        // Cari user berdasarkan username atau email
        $targetUser = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $targetUser) {
            return back()->with('error', "Pengguna dengan username atau email '{$identifier}' tidak ditemukan.");
        }

        if (! $targetUser->isActive()) {
            return back()->with('error', 'Gagal menambahkan: Akun pengguna yang dituju sedang dinonaktifkan.');
        }

        // Periksa apakah sudah menjadi anggota
        $existing = $book->members()->where('users.id', $targetUser->id)->first();
        if ($existing) {
            if ($existing->pivot->status === 'active') {
                return back()->with('error', "Pengguna '{$targetUser->name}' sudah menjadi anggota aktif di buku ini.");
            }
            // Aktifkan kembali
            $book->members()->updateExistingPivot($targetUser->id, [
                'role' => $request->role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        } else {
            // Tambah anggota baru
            $book->members()->attach($targetUser->id, [
                'role' => $request->role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'action' => 'ADD_MEMBER',
            'module' => 'Multi-Ledger',
            'description' => "Pengguna {$targetUser->name} ({$targetUser->username}) ditambahkan sebagai " . strtoupper($request->role) . " pada buku {$book->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Berhasil menambahkan {$targetUser->name} sebagai anggota buku kas!");
    }

    /**
     * Update member role.
     */
    public function update(Request $request, Book $book, User $member): RedirectResponse
    {
        $this->authorize('manageMembers', $book);

        $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        // Lindungi status pemilik
        if ((int) $book->owner_id === (int) $member->id) {
            return back()->with('error', 'Peran Pemilik Buku (Owner) hanya dapat diubah melalui fitur Transfer Kepemilikan.');
        }

        $book->members()->updateExistingPivot($member->id, [
            'role' => $request->role,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'action' => 'UPDATE_MEMBER_ROLE',
            'module' => 'Multi-Ledger',
            'description' => "Peran anggota {$member->name} pada buku {$book->name} diubah menjadi " . strtoupper($request->role) . ".",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Peran anggota {$member->name} berhasil diperbarui.");
    }

    /**
     * Remove member from the book (without deleting their transaction history).
     */
    public function destroy(Request $request, Book $book, User $member): RedirectResponse
    {
        $this->authorize('manageMembers', $book);

        if ((int) $book->owner_id === (int) $member->id) {
            return back()->with('error', 'Pemilik Buku (Owner) tidak dapat dikeluarkan dari buku kas.');
        }

        // Hapus hubungan dari tabel pivot (riwayat transaksi yang dicatat tetap utuh di tabel transactions)
        $book->members()->detach($member->id);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'action' => 'REMOVE_MEMBER',
            'module' => 'Multi-Ledger',
            'description' => "Anggota {$member->name} dikeluarkan dari buku keuangan {$book->name}. Seluruh riwayat transaksi yang pernah dicatat tetap tersimpan aman.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Anggota {$member->name} berhasil dikeluarkan dari buku kas.");
    }

    /**
     * Transfer book ownership.
     */
    public function transferOwnership(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('transferOwnership', $book);

        $request->validate([
            'new_owner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $newOwner = User::findOrFail($request->new_owner_id);

        if ((int) $newOwner->id === (int) Auth::id()) {
            return back()->with('error', 'Anda sudah merupakan pemilik buku keuangan ini.');
        }

        $success = $this->ledgerService->transferOwnership($book, Auth::user(), $newOwner);

        if (! $success) {
            return back()->with('error', 'Gagal memindahkan kepemilikan: Calon pemilik harus merupakan anggota aktif pada buku ini.');
        }

        return redirect()->route('books.members', $book)
            ->with('success', "Kepemilikan buku '{$book->name}' berhasil dialihkan kepada {$newOwner->name}.");
    }
}
