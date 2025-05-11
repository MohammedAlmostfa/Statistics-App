<?php

namespace App\Policies;

use App\Models\User;

class WhatsAppPolicy
{

    public function GetWhatssapMessage(User $user): bool
    {
        return $user->hasPermissionTo('whatsappMessage.list');
    }

}
