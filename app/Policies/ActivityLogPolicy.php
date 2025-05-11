<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    public function GetActivitesLog(User $user): bool
    {
        return $user->hasPermissionTo('activiteLog.list');
    }
}
