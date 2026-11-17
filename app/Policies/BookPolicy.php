<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Determine whether the user can view the book.
     */
    public function view(User $user, Book $book): bool
    {
        return $book->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Determine whether the user can update the book details.
     */
    public function update(User $user, Book $book): bool
    {
        $member = $book->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        return $member && in_array($member->pivot->role, ['owner', 'admin'], true);
    }

    /**
     * Determine whether the user can delete/archive the book.
     */
    public function delete(User $user, Book $book): bool
    {
        return (int) $book->owner_id === (int) $user->id;
    }

    /**
     * Determine whether the user can manage members of the book.
     */
    public function manageMembers(User $user, Book $book): bool
    {
        $member = $book->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        return $member && in_array($member->pivot->role, ['owner', 'admin'], true);
    }

    /**
     * Determine whether the user can transfer ownership of the book.
     */
    public function transferOwnership(User $user, Book $book): bool
    {
        return (int) $book->owner_id === (int) $user->id;
    }
}
