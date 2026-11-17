<?php

namespace App\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ledger\StoreBookRequest;
use App\Http\Requests\Ledger\UpdateBookRequest;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Book;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display a listing of books the user has access to.
     */
    public function index(): View
    {
        $user = Auth::user();

        $books = $user->books()
            ->withCount('members')
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);

        // Tambahkan atribut kalkulasi saldo dinamis pada setiap buku
        foreach ($books as $book) {
            $book->current_balance = $this->ledgerService->calculateBalance($book);
        }

        return view('books.index', [
            'books' => $books,
            'activeBookId' => session('active_book_id'),
        ]);
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        return view('books.create');
    }

    /**
     * Store a newly created book.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = $this->ledgerService->createBook(Auth::user(), $request->validated());

        // Otomatis aktifkan buku yang baru saja dibuat
        session(['active_book_id' => $book->id]);

        return redirect()->route('books.index')
            ->with('success', "Buku keuangan '{$book->name}' berhasil dibuat dan dijadikan buku aktif!");
    }

    /**
     * Switch the active book in session.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['book_id' => 'required|integer|exists:books,id']);

        $book = Book::findOrFail($request->book_id);
        $user = Auth::user();

        if (! $user->isMemberOf($book->id) && ! $user->isAdmin()) {
            return back()->with('error', 'Akses ditolak: Anda bukan anggota aktif dari buku keuangan ini.');
        }

        session(['active_book_id' => $book->id]);

        ActivityLog::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'SWITCH_BOOK',
            'module' => 'Multi-Ledger',
            'description' => "Pengguna {$user->name} beralih ke buku keuangan: {$book->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Buku keuangan aktif berhasil diubah ke '{$book->name}'.");
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        return view('books.edit', compact('book'));
    }

    /**
     * Update the specified book.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $oldValues = $book->only(['name', 'description']);

        $book->update($request->validated());

        AuditLog::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'auditable_type' => Book::class,
            'auditable_id' => $book->id,
            'event' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $book->only(['name', 'description']),
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'action' => 'UPDATE_BOOK',
            'module' => 'Multi-Ledger',
            'description' => "Pengguna memperbarui informasi buku keuangan: {$book->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('books.index')
            ->with('success', "Informasi buku keuangan '{$book->name}' berhasil diperbarui.");
    }

    /**
     * Remove / archive the specified book.
     */
    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $user = Auth::user();

        // Cegah menghapus jika ini adalah satu-satunya buku aktif pengguna
        if ($user->activeBooks()->count() <= 1 && ! $user->isAdmin()) {
            return back()->with('error', 'Gagal menghapus: Anda harus memiliki minimal satu buku keuangan aktif.');
        }

        $bookName = $book->name;
        $book->delete();

        // Jika buku yang dihapus sedang aktif di session, alihkan ke buku lain
        if ((int) session('active_book_id') === (int) $book->id) {
            $nextBook = $user->activeBooks()->first();
            if ($nextBook) {
                session(['active_book_id' => $nextBook->id]);
            } else {
                session()->forget('active_book_id');
            }
        }

        AuditLog::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'auditable_type' => Book::class,
            'auditable_id' => $book->id,
            'event' => 'deleted',
            'old_values' => ['name' => $bookName],
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'book_id' => null,
            'action' => 'DELETE_BOOK',
            'module' => 'Multi-Ledger',
            'description' => "Buku keuangan '{$bookName}' telah diarsipkan oleh {$user->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('books.index')
            ->with('success', "Buku keuangan '{$bookName}' berhasil diarsipkan.");
    }
}
