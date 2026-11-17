<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCategoryRequest;
use App\Http\Requests\Finance\UpdateCategoryRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Daftar kategori untuk buku yang sedang aktif.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $activeBook = $this->getActiveBook();

        $filter = $request->query('type', 'all');  // 'all' | 'income' | 'expense'
        $status = $request->query('status', 'active'); // 'active' | 'archived'

        $query = Category::where('book_id', $activeBook->id);

        if (in_array($filter, ['income', 'expense'])) {
            $query->where('type', $filter);
        }

        if (in_array($status, ['active', 'archived'])) {
            $query->where('status', $status);
        }

        $categories = $query->orderBy('type')->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total_income'   => Category::where('book_id', $activeBook->id)->where('type', 'income')->count(),
            'total_expense'  => Category::where('book_id', $activeBook->id)->where('type', 'expense')->count(),
            'total_archived' => Category::where('book_id', $activeBook->id)->where('status', 'archived')->count(),
        ];

        return view('categories.index', compact('categories', 'activeBook', 'filter', 'status', 'stats'));
    }

    /**
     * Form tambah kategori baru.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);

        $activeBook = $this->getActiveBook();

        return view('categories.create', compact('activeBook'));
    }

    /**
     * Simpan kategori baru.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $activeBook = $this->getActiveBook();

        Category::create([
            'book_id'     => $activeBook->id,
            'name'        => trim($request->name),
            'type'        => $request->type,
            'description' => $request->description ? trim($request->description) : null,
            'status'      => 'active',
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . trim($request->name) . '" berhasil ditambahkan.');
    }

    /**
     * Form edit kategori.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        $activeBook = $this->getActiveBook();

        return view('categories.edit', compact('category', 'activeBook'));
    }

    /**
     * Simpan perubahan kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        // Pastikan kategori masih di buku aktif (IDOR protection)
        $this->ensureCategoryBelongsToActiveBook($category);

        $category->update([
            'name'        => trim($request->name),
            'type'        => $request->type,
            'description' => $request->description ? trim($request->description) : null,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $category->name . '" berhasil diperbarui.');
    }

    /**
     * Arsipkan kategori (soft-delete logis).
     * Kategori yang sudah digunakan transaksi tidak bisa dihapus permanen.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $this->ensureCategoryBelongsToActiveBook($category);

        $hasTransactions = $category->transactions()->exists();

        if ($hasTransactions) {
            // Arsipkan saja jika sudah digunakan
            $category->update(['status' => 'archived']);

            return redirect()->route('categories.index')
                ->with('warning', 'Kategori "' . $category->name . '" diarsipkan karena sudah digunakan oleh transaksi.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }

    /**
     * Arsipkan kategori secara eksplisit.
     */
    public function archive(Category $category): RedirectResponse
    {
        $this->authorize('archive', $category);
        $this->ensureCategoryBelongsToActiveBook($category);

        if ($category->status === 'archived') {
            return redirect()->route('categories.index')
                ->with('info', 'Kategori sudah dalam status arsip.');
        }

        $category->update(['status' => 'archived']);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $category->name . '" berhasil diarsipkan.');
    }

    /**
     * Pulihkan kategori dari arsip.
     */
    public function restore(Category $category): RedirectResponse
    {
        $this->authorize('restore', $category);
        $this->ensureCategoryBelongsToActiveBook($category);

        if ($category->status === 'active') {
            return redirect()->route('categories.index')
                ->with('info', 'Kategori sudah dalam status aktif.');
        }

        $category->update(['status' => 'active']);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $category->name . '" berhasil dipulihkan.');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function getActiveBook(): Book
    {
        $bookId = session('active_book_id');

        abort_if(! $bookId, 403, 'Tidak ada buku kas aktif. Pilih buku kas terlebih dahulu.');

        $book = auth()->user()->books()->find($bookId);

        abort_if(! $book, 403, 'Buku kas tidak ditemukan atau Anda bukan anggota buku ini.');

        return $book;
    }

    private function ensureCategoryBelongsToActiveBook(Category $category): void
    {
        $activeBookId = session('active_book_id');

        abort_if($category->book_id !== $activeBookId, 403, 'Akses ditolak. Kategori tidak termasuk dalam buku kas aktif.');
    }
}
