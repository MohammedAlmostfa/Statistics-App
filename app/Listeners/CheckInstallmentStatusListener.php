<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Events\InstallmentPaidEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener to check the installment payment status
 * and update the installment record when fully paid.
 */
class CheckInstallmentStatusListener
{
    /**
     * Handle the event when an installment payment is made.
     *
     * @param InstallmentPaidEvent $event The event triggered after an installment is paid.
     * @return void
     */
    public function handle(InstallmentPaidEvent $event)
    {
        $installment = $event->installment;

        // Calculate the total paid amount, including the first payment
        $totalAmount = $installment->installmentPayments()->sum('amount');
        $totalPaid = $installment->first_pay + $totalAmount;

        // Get the total price of the installment
        $totalPrice = $installment->receiptProduct->total_price;

        /**
         * If the total amount paid matches the installment price,
         * mark the installment as "Paid".
         */
        if ($totalPrice == $totalPaid) {
            $installment->update(['status' => 'مسدد']); // Status update to "Paid"
        }
    }
}
