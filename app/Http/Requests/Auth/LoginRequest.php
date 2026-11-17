<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->input('login'));
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        // Tentukan apakah input berupa email atau username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        // Cari user terlebih dahulu untuk memeriksa status akun
        $user = User::where($fieldType, $loginInput)->first();

        if ($user && $user->status === 'inactive') {
            RateLimiter::hit($this->throttleKey());

            LoginLog::create([
                'user_id' => $user->id,
                'username_attempted' => $loginInput,
                'status' => 'failed',
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'failure_reason' => 'Akun nonaktif',
            ]);

            throw ValidationException::withMessages([
                'login' => 'Akun Anda dinonaktifkan oleh administrator. Silakan hubungi admin sistem.',
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey());

            LoginLog::create([
                'user_id' => $user?->id,
                'username_attempted' => $loginInput,
                'status' => 'failed',
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'failure_reason' => 'Kredensial tidak cocok',
            ]);

            throw ValidationException::withMessages([
                'login' => 'Username/email atau password yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Catat keberhasilan login
        LoginLog::create([
            'user_id' => Auth::id(),
            'username_attempted' => $loginInput,
            'status' => 'success',
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}
