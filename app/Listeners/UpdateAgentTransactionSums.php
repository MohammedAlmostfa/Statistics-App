<?php
namespace App\Listeners;

use App\Models\FinancialTransaction;
use App\Events\FinancialTransactionEdit;

class UpdateAgentTransactionSums
{
    public function handle(FinancialTransactionEdit $event)
    {
        $transaction = $event->transaction;
        $agentId = $transaction->agent_id;


        $LastSumAmount = $transaction->sum_amount;
        $affectedTransactions = FinancialTransaction::where('agent_id', $agentId)
            ->where('id', '>', $transaction->id)
            ->orderBy('id')
            ->get();

        foreach ($affectedTransactions as $trans) {
            if ($trans->type == 'تسديد فاتورة شراء') {
                $LastSumAmount -= $trans->paid_amount;
            } elseif($trans->type == 'دين فاتورة شراء') {
                $LastSumAmount += ($trans->total_amount);
            } else {
                $LastSumAmount += ($trans->total_amount - $trans->discount_amount - $trans->paid_amount);

            }

            $trans->update(['sum_amount' => $LastSumAmount]);
        }
    }
}
