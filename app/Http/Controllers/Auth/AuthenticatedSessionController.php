<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isOrangTua()) {
                return redirect()->route('dashboard.senior');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Inisialisasi buku aktif di session
        $firstBook = $user->activeBooks()->first();
        if ($firstBook) {
            session(['active_book_id' => $firstBook->id]);
        }

        // Catat aktivitas login
        ActivityLog::create([
            'user_id' => $user->id,
            'book_id' => $firstBook?->id,
            'action' => 'LOGIN',
            'module' => 'Autentikasi',
            'description' => "Pengguna {$user->name} ({$user->username}) berhasil masuk ke sistem.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Pengalihan berbasis role sistem
        if ($user->isOrangTua()) {
            return redirect()->intended(route('dashboard.senior'));
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'book_id' => session('active_book_id'),
                'action' => 'LOGOUT',
                'module' => 'Autentikasi',
                'description' => "Pengguna {$user->name} ({$user->username}) keluar dari sistem.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari akun Finora.');
    }
}
