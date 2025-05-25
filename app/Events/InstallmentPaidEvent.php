<?php

namespace App\Events;

use App\Models\Installment;

class InstallmentPaidEvent
{
    public Installment $installment;

    public function __construct(Installment $installment)
    {
        $this->installment = $installment;
    }
}
