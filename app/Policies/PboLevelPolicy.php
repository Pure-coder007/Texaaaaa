<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PboLevel;
use Illuminate\Auth\Access\HandlesAuthorization;

class PboLevelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pbo::level');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('view_pbo::level');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pbo::level');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('update_pbo::level');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('delete_pbo::level');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pbo::level');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('force_delete_pbo::level');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pbo::level');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('restore_pbo::level');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pbo::level');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PboLevel $pboLevel): bool
    {
        return $user->can('replicate_pbo::level');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pbo::level');
    }
}
