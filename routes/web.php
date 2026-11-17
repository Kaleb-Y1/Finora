<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Redirection root
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isOrangTua()
            ? redirect()->route('dashboard.senior')
            : redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// --- Rute Autentikasi (Guest Only) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleLoginController::class, 'redirect'])->middleware('throttle:10,1')->name('auth.google.redirect');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleLoginController::class, 'callback'])->middleware('throttle:10,1')->name('auth.google.callback');
});

// --- Rute Terproteksi (Harus Login) ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard Pengalihan Cerdas
    Route::get('/dashboard', function () {
        if (auth()->user()->isOrangTua()) {
            return redirect()->route('dashboard.senior');
        }
        return app(\App\Http\Controllers\Finance\DashboardController::class)->index(request(), app(\App\Services\FinanceService::class));
    })->name('dashboard');

    Route::get('/dashboard/senior', function () {
        if (! auth()->user()->isOrangTua() && ! auth()->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }
        return app(\App\Http\Controllers\Finance\DashboardController::class)->index(request(), app(\App\Services\FinanceService::class));
    })->name('dashboard.senior');

    // Switcher Buku Kas Aktif
    Route::post('/ledger/switch', [\App\Http\Controllers\Ledger\BookController::class, 'switch'])->name('ledger.switch');

    // Manajemen Buku Kas (Multi-Ledger)
    Route::resource('books', \App\Http\Controllers\Ledger\BookController::class);

    // Pengelolaan Anggota Buku Kas
    Route::prefix('books/{book}/members')->name('books.members')->group(function () {
        Route::get('/', [\App\Http\Controllers\Ledger\BookMemberController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Ledger\BookMemberController::class, 'store'])->name('.store');
        Route::put('/{member}', [\App\Http\Controllers\Ledger\BookMemberController::class, 'update'])->name('.update');
        Route::delete('/{member}', [\App\Http\Controllers\Ledger\BookMemberController::class, 'destroy'])->name('.destroy');
        Route::post('/transfer-ownership', [\App\Http\Controllers\Ledger\BookMemberController::class, 'transferOwnership'])->name('.transfer');
    });

    Route::middleware('book.active')->group(function () {
        Route::get('transactions', [\App\Http\Controllers\Finance\TransactionController::class,'index'])->name('transactions.index');
        Route::get('transactions/create', [\App\Http\Controllers\Finance\TransactionController::class,'create'])->name('transactions.create');
        Route::post('transactions', [\App\Http\Controllers\Finance\TransactionController::class,'store'])->name('transactions.store');
        Route::get('transactions/{transaction}/edit', [\App\Http\Controllers\Finance\TransactionController::class,'edit'])->name('transactions.edit');
        Route::put('transactions/{transaction}', [\App\Http\Controllers\Finance\TransactionController::class,'update'])->name('transactions.update');
        Route::delete('transactions/{transaction}', [\App\Http\Controllers\Finance\TransactionController::class,'destroy'])->name('transactions.destroy');
        Route::get('attachments/{attachment}/download', [\App\Http\Controllers\Finance\TransactionController::class,'download'])->name('attachments.download');
        Route::get('transactions/income',fn()=>redirect()->route('transactions.index',['type'=>'income']))->name('transactions.income.index');
        Route::get('transactions/income/create',fn()=>redirect()->route('transactions.create',['type'=>'income']))->name('transactions.income.create');
        Route::get('transactions/expense',fn()=>redirect()->route('transactions.index',['type'=>'expense']))->name('transactions.expense.index');
        Route::get('transactions/expense/create',fn()=>redirect()->route('transactions.create',['type'=>'expense']))->name('transactions.expense.create');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\ReportController::class,'index'])->middleware('book.active')->name('index');
        Route::get('/csv', [\App\Http\Controllers\Finance\ReportController::class,'csv'])->middleware('book.active')->name('csv');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', fn () => view('dashboard.umum'))->name('show');
    });

    // Admin-only: backend authorization applies to every endpoint.
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\SystemController::class, 'users'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\SystemController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\SystemController::class, 'updateUser'])->name('users.update');
        Route::get('/audit', [\App\Http\Controllers\Admin\SystemController::class, 'audit'])->name('audit.index');
        Route::get('/logs/activity', [\App\Http\Controllers\Admin\SystemController::class, 'activity'])->name('logs.activity');
        Route::get('/logs/error', [\App\Http\Controllers\Admin\SystemController::class, 'errors'])->name('logs.error');
        Route::get('/logs/login', [\App\Http\Controllers\Admin\SystemController::class, 'logins'])->name('logs.login');
        Route::get('/backup', [\App\Http\Controllers\Admin\SystemController::class, 'backups'])->name('backup.index');
        Route::post('/backup', [\App\Http\Controllers\Admin\SystemController::class, 'createBackup'])->name('backup.store');
        Route::get('/backup/{backup}/download', [\App\Http\Controllers\Admin\SystemController::class, 'downloadBackup'])->name('backup.download');
    });
});
