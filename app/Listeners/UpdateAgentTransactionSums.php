<?php
namespace App\Listeners;

use App\Events\FinancialTransactionUpdated;
use App\Models\FinancialTransaction;

class UpdateAgentTransactionSums
{
    public function handle(FinancialTransactionUpdated $event)
    {
        $transaction = $event->transaction;
        $agentId = $transaction->agent_id;

        // استرجاع المعاملات اللاحقة
        $LastSumAmount = $transaction->sum_amount;
        $affectedTransactions = FinancialTransaction::where('agent_id', $agentId)
            ->where('id', '>', $transaction->id)
            ->orderBy('id')
            ->get();

        foreach ($affectedTransactions as $trans) {
            if ($trans->type == 'تسديد فاتورة شراء') {
                $LastSumAmount -= $trans->paid_amount;
            } else {
                $LastSumAmount += ($trans->total_amount - $trans->discount_amount - $trans->paid_amount);
            }

            $trans->update(['sum_amount' => $LastSumAmount]);
        }
    }
}
