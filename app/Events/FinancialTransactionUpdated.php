<?php

namespace App\Events;

use App\Models\FinancialTransaction;
use Illuminate\Foundation\Events\Dispatchable;

class FinancialTransactionUpdated
{
    use Dispatchable;

    public $transaction;

    public function __construct(FinancialTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
