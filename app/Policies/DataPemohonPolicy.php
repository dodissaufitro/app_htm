<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DataPemohon;
use Illuminate\Auth\Access\HandlesAuthorization;

class DataPemohonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_kelengkapan::data');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function view(User $user, DataPemohon $dataPemohon): bool
    {
        // Check basic permission first
        if (!$user->can('view_kelengkapan::data')) {
            return false;
        }

        // Check if user can access this specific status
        return $this->canAccessDataPemohonStatus($user, $dataPemohon);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_kelengkapan::data');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function update(User $user, DataPemohon $dataPemohon): bool
    {
        // Check basic permission first
        if (!$user->can('update_kelengkapan::data')) {
            return false;
        }

        // Check if user can access this specific status
        return $this->canAccessDataPemohonStatus($user, $dataPemohon);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function delete(User $user, DataPemohon $dataPemohon): bool
    {
        // Check basic permission first
        if (!$user->can('delete_kelengkapan::data')) {
            return false;
        }

        // Check if user can access this specific status
        return $this->canAccessDataPemohonStatus($user, $dataPemohon);
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_kelengkapan::data');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function forceDelete(User $user, DataPemohon $dataPemohon): bool
    {
        return $user->can('force_delete_kelengkapan::data');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_kelengkapan::data');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function restore(User $user, DataPemohon $dataPemohon): bool
    {
        return $user->can('restore_kelengkapan::data');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_kelengkapan::data');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    public function replicate(User $user, DataPemohon $dataPemohon): bool
    {
        return $user->can('replicate_kelengkapan::data');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_kelengkapan::data');
    }

    /**
     * Helper method to check if user can access data pemohon based on status
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return bool
     */
    private function canAccessDataPemohonStatus(User $user, DataPemohon $dataPemohon): bool
    {
        // If no allowed status is configured, user can access all
        if (empty($user->allowed_status)) {
            return true;
        }

        // Check if the data pemohon's status is in user's allowed status list
        return in_array($dataPemohon->status_permohonan, $user->allowed_status);
    }
}
