<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleLoginController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 503);
        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['login' => 'Login Google dibatalkan atau ditolak.']);
        }
        try {
            // Socialite menggunakan state session untuk melindungi OAuth callback.
            $google = Socialite::driver('google')->user();
            $googleId = (string) $google->getId();
            $email = $google->getEmail();
            $verified = $google->user['email_verified'] ?? false;
            if (!$googleId || !$email || !in_array($verified, [true, 'true', 1], true)) {
                return redirect()->route('login')->withErrors(['login' => 'Email Google harus terverifikasi.']);
            }

            $user = DB::transaction(function () use ($googleId, $email, $google) {
                $existing = User::where('google_id', $googleId)->lockForUpdate()->first();
                if ($existing) return $existing;
                // Jangan tautkan otomatis akun lokal hanya berdasarkan email.
                if (User::where('email', $email)->exists()) return null;
                return User::create([
                    'name' => Str::limit($google->getName() ?: 'Pengguna Finora', 255, ''),
                    'username' => 'google_' . Str::lower(Str::random(20)),
                    'google_id' => $googleId,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Str::random(64),
                    'role' => 'umum',
                    'status' => 'active',
                ]);
            });
            if (!$user) return redirect()->route('login')->withErrors(['login' => 'Email sudah terdaftar. Masuk dengan metode sebelumnya; penautan akun memerlukan verifikasi.']);
            if (!$user->isActive()) return redirect()->route('login')->withErrors(['login' => 'Akun dinonaktifkan. Hubungi administrator.']);
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget('active_book_id');
            $book = $user->activeBooks()->first();
            if ($book) $request->session()->put('active_book_id', $book->id);
            return redirect()->route($user->isOrangTua() ? 'dashboard.senior' : 'dashboard');
        } catch (Throwable $e) {
            Log::warning('Google OAuth gagal', ['exception_type' => get_class($e)]);
            return redirect()->route('login')->withErrors(['login' => 'Login Google gagal. Coba kembali.']);
        }
    }
}
