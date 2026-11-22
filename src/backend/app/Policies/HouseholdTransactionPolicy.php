<?php

namespace App\Policies;

use App\Models\HouseholdTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HouseholdTransactionPolicy
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
    public function view(User $user, HouseholdTransaction $householdTransaction): bool
    {
        return ($user->id === $householdTransaction->user_id);
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
    public function update(User $user, HouseholdTransaction $householdTransaction): bool
    {
        return ($user->id === $householdTransaction->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HouseholdTransaction $householdTransaction): bool
    {
        return ($user->id === $householdTransaction->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, HouseholdTransaction $householdTransaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, HouseholdTransaction $householdTransaction): bool
    {
        return false;
    }
}
