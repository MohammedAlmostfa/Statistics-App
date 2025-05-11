<?php

namespace App\Policies;

use App\Models\User;

class FinacialReportPolicy
{

    public function GetFinacialReport(User $user): bool
    {
        return $user->hasPermissionTo('financialReport.list');
    }
}
