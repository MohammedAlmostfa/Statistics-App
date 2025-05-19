<?php

namespace App\Events;

use App\Models\DebtPayment;

class DebtPaymentProcessed
{


    public DebtPayment $debtPayment;

    public function __construct(DebtPayment $debtPayment)
    {
        $this->debtPayment = $debtPayment;
    }
}
