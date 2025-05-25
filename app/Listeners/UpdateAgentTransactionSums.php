<?php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;
use App\Events\FinancialTransactionEdit;

/**
 * Listener to update an agent's financial transaction sums
 * when a transaction is edited or deleted.
 */
class UpdateAgentTransactionSums
{
    /**
     * Handle the event to update agent transaction sums.
     *
     * @param FinancialTransactionEdit $event The event triggered for a financial transaction update or deletion.
     * @return void
     */
    public function handle(FinancialTransactionEdit $event)
    {
        $financialTransaction = $event->financialTransaction;
        $agentId = $financialTransaction->agent_id;

        /**
         * Determine the `sum_amount` value based on event type:
         * - If the transaction is deleted, retrieve the last `sum_amount` of previous transactions.
         * - update , use the current transaction's `sum_amount`.
         */
        if ($event->type == 'delete') {
            $SumAmount = optional(FinancialTransaction::where('agent_id', $agentId)
                ->where('id', '<', $financialTransaction->id)
                ->latest()
                ->first())->sum_amount ?? 0;
        } else {
            $SumAmount = $financialTransaction->sum_amount;
        }

        /**
         * Retrieve all affected transactions that need updating.
         */
        $affectedTransactions = FinancialTransaction::where('agent_id', $agentId)
            ->where('id', '>', $financialTransaction->id)
            ->orderBy('id')
            ->get();

        /**
         * Iterate through affected transactions and update their `sum_amount`
         * based on transaction type.
         */
        foreach ($affectedTransactions as $trans) {
            $SumAmount = match ($trans->type) {
                'تسديد فاتورة شراء' => $SumAmount - $trans->paid_amount,
                'دين فاتورة شراء' => $SumAmount + $trans->total_amount,
                default => $SumAmount + ($trans->total_amount - $trans->discount_amount - $trans->paid_amount)
            };
            $trans->update(['sum_amount' => $SumAmount]);
        }
    }
}
