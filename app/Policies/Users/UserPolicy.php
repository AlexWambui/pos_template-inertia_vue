<?php

namespace App\Policies\Users;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view themselves
        if ($user->id === $model->id) {
            return true;
        }
        
        // Super admins can view anyone
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admins can view anyone except super admins
        if ($user->isAdmin()) {
            return !$model->isSuperAdmin();
        }
        
        // Cashiers can only view customers
        if ($user->isCashier()) {
            return $model->isCustomer();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves
        if ($user->id === $model->id) {
            return true;
        }
        
        // Super admins can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admins can update anyone except super admins
        if ($user->isAdmin()) {
            return !$model->isSuperAdmin();
        }
        
        // Cashiers can only update customers
        if ($user->isCashier()) {
            return $model->isCustomer();
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $auth_user, User $user): bool
    {
        // Users can't delete themselves
        if ($auth_user->id === $user->id) {
            return false;
        }

        // Only super admins can delete super admins
        if ($user->isSuperAdmin() && !$auth_user->isSuperAdmin()) {
            return false;
        }

        return $auth_user->isSuperAdmin() || $auth_user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
