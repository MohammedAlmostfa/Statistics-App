<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Events\InstallmentPaidEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckInstallmentStatusListener implements ShouldQueue
{
    public function handle(InstallmentPaidEvent $event)
    {

        $installment = $event->installment;


        $totalAmount = $installment->installmentPayments()->sum('amount');
        $totalPaid = $installment->first_pay + $totalAmount;

        $totalPrice = $installment->receiptProduct->total_price;

        if ($totalPrice == $totalPaid) {
            $installment->update(['status' => 'مسدد']);
        }
    }
}
