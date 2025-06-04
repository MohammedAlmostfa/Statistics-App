<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Customer;
use App\Models\DebtPayment;
use Illuminate\Console\Command;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendWhatsAppNotification;

class CheckLatePayments extends Command
{
    protected $signature = 'check:late-payments';
    protected $description = 'Send WhatsApp reminders for overdue installment and debt payments.';

    public function handle(): int
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        $customers = Customer::all();

        foreach ($customers as $customer) {
            $this->checkInstallments($customer, $oneMonthAgo);
            $this->checkDebts($customer, $oneMonthAgo);
        }

        return Command::SUCCESS;
    }

    protected function checkInstallments($customer, $oneMonthAgo)
    {
        $latestInstallmentPayment = InstallmentPayment::whereHas('installment.receiptProduct.receipt', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
        ->whereHas('installment', function ($query) {
            $query->where('status', 1);
        })
        ->latest('payment_date')
        ->value('payment_date');

        if ($latestInstallmentPayment && $latestInstallmentPayment->payment_date) {
            $paymentDate = Carbon::parse($latestInstallmentPayment->payment_date);
            $daysDiff = $paymentDate->diffInDays(Carbon::today());

            if ($daysDiff ==30) {
                $lastDate = $paymentDate->format('Y-m-d');
                $msg = "السلام عليكم {$customer->name}، لم نقم بتسجيل أي دفعة أقساط منذ {$lastDate}. نذكرك بضرورة السداد لتفادي التأخير، وذلك لصالح محمد حمدان للإلكترونيات.";

                Notification::send($customer, new SendWhatsAppNotification($msg));

            }
        }
    }

    protected function checkDebts($customer, $oneMonthAgo)
    {
        $debts = Debt::where('customer_id', $customer->id)
                    ->where('remaining_debt', '>', 0)
                    ->get();

        foreach ($debts as $debt) {
            $latestDebtPayment = $debt->debtPayments()->latest('id')->first();
            $lastPaymentDate = $latestDebtPayment?->payment_date ?? $debt->debt_date;

            if ($lastPaymentDate) {
                $date = Carbon::parse($lastPaymentDate);
                $daysDiff = $date->diffInDays(Carbon::today());
                if ($daysDiff == 30) {
                    $formattedDate = $date->format('Y-m-d');
                    $msg = "مرحبًا {$customer->name}، لديك دين لم يتم سداده منذ {$formattedDate}. نرجو السداد في أقرب وقت، وذلك لصالح محمد حمدان للإلكترونيات.";

                    Notification::send($customer, new SendWhatsAppNotification($msg));

                }
            }
        }
    }
}
