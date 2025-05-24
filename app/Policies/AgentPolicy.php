<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgentPolicy
{
    public function deleteAgent(User $user): bool
    {
        return $user->hasPermissionTo('agent.delete');
    }
}
