<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    /**
     * Check if the user has permission to list users.
     */
    public function getUser(User $user): bool
    {
        return $user->hasPermissionTo('user.list');
    }

    /**
     * Check if the user has permission to create a user.
     */
    public function createUser(User $user): bool
    {
        return $user->hasPermissionTo('user.create');
    }

    /**
     * Check if the user has permission to update a user.
     */
    public function updateUser(User $user): bool
    {
        return $user->hasPermissionTo('user.update');
    }

    /**
     * Check if the user has permission to delete a user.
     */
    public function deleteUser(User $user): bool
    {
        return $user->hasPermissionTo('user.delete');
    }

    /**
     * Check if the user has permission to view user details.
     */
    public function showUser(User $user): bool
    {
        return $user->hasPermissionTo('user.details');
    }
}
