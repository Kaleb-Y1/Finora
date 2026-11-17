<?php

namespace App\Http\Middleware;

use App\Models\Book;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBookSelected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $activeBookId = session('active_book_id');

        // Verifikasi apakah buku aktif saat ini valid dan user masih menjadi anggota aktif
        if ($activeBookId) {
            $isMember = $user->activeBooks()->where('books.id', $activeBookId)->exists();
            if (! $isMember) {
                $activeBookId = null;
                session()->forget('active_book_id');
            }
        }

        // Jika belum ada buku aktif di session, ambil buku pertama yang aktif
        if (! $activeBookId) {
            $defaultBook = $user->activeBooks()->first();
            if ($defaultBook) {
                session(['active_book_id' => $defaultBook->id]);
            } else {
                // Pengguna belum memiliki buku sama sekali
                if (! $request->routeIs('books.create', 'books.store', 'logout', 'profile.*')) {
                    return redirect()->route('books.create')
                        ->with('info', 'Selamat datang di Finora! Silakan buat buku keuangan pertama Anda untuk mulai mencatat transaksi.');
                }
            }
        }

        return $next($request);
    }
}
