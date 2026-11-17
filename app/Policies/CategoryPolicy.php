<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Semua operasi kategori hanya berlaku untuk anggota buku yang aktif.
     * Isolasi data dilakukan di controller, policy ini fokus pada kepemilikan buku.
     */

    public function viewAny(User $user): bool
    {
        return $user->hasActiveBook();
    }

    public function view(User $user, Category $category): bool
    {
        return $this->userBelongsToBook($user, $category);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveBook();
    }

    public function update(User $user, Category $category): bool
    {
        return $this->userBelongsToBook($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->userBelongsToBook($user, $category);
    }

    public function archive(User $user, Category $category): bool
    {
        return $this->userBelongsToBook($user, $category);
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->userBelongsToBook($user, $category);
    }

    private function userBelongsToBook(User $user, Category $category): bool
    {
        $activeBookId = session('active_book_id');

        // Kategori harus berada di buku yang aktif
        if ($category->book_id !== $activeBookId) {
            return false;
        }

        // User harus anggota buku tersebut
        return $user->books()->where('books.id', $category->book_id)->exists();
    }
}
