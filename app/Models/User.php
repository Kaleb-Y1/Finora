<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'google_id',
        'email',
        'password',
        'role',
        'profile_photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function ownedBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'owner_id');
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_user')
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function activeBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_user')
            ->wherePivot('status', 'active')
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUmum(): bool
    {
        return $this->role === 'umum';
    }

    public function isOrangTua(): bool
    {
        return $this->role === 'orang_tua';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMemberOf(int $bookId): bool
    {
        return $this->activeBooks()->where('books.id', $bookId)->exists();
    }

    public function getBookRole(int $bookId): ?string
    {
        $book = $this->books()->where('books.id', $bookId)->first();
        return $book ? $book->pivot->role : null;
    }
}
