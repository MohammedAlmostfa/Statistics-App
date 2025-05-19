<?php

namespace App\Listeners;

use App\Models\Debt;
use App\Events\DebtPaymentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateRemainingDebt
{


    /**
     * Handle the event.
     */
    public function handle(DebtPaymentProcessed $event): void
    {
        $debt = Debt::find($event->debtPayment->debt_id);

        if ($debt) {
            $debt->remaining_debt -= $event->debtPayment->amount;
            $debt->save();
        }
    }
}
