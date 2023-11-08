<?php

namespace App\Policies;

use App\Models\CannedReply;
use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\Response;
// use Illuminate\Support\Facades\Log;

class CannedReplyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CannedReply $cannedReply): bool
    {
        if (
            $user->id == $cannedReply->department->user_id  // Canned Reply Owner
            || $user->level >= 50                           // Admin or higher
        ) {
            return true;
        }
        
        return false; 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, CannedReply $cannedReply, Department $department): bool
    {

        if (
            $user->id == $department->user_id  // Canned Reply Owner
            || $user->level >= 50                           // Admin or higher
        ) {
            return true;
        }
        

        // do stuff
        return false; 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CannedReply $cannedReply): bool
    {
        //
        if (
            $user->id == $cannedReply->department->user_id  // Canned Reply Owner
            || $user->level >= 50                           // Admin or higher
        ) {
            return true;
        }
        
        return false; 
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CannedReply $cannedReply): bool
    {
        if (
            $user->id == $cannedReply->department->user_id  // Canned Reply Owner
            || $user->level >= 50                           // Admin or higher
        ) {
            return true;
        }
        
        return false; 
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CannedReply $cannedReply): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CannedReply $cannedReply): bool
    {
        //
    }
}
