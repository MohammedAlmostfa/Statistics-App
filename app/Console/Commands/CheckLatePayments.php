<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Customer;
use App\Models\DebtPayment;
use Illuminate\Console\Command;
use App\Models\InstallmentPayment;
use App\Models\Receipt;
use App\Notifications\SendWhatsAppNotification;

class CheckLatePayments extends Command
{
    // Command signature to be called via artisan CLI
    protected $signature = 'check:late-payments';

    // Description shown when listing artisan commands
    protected $description = 'Send WhatsApp reminders for overdue installment and debt payments.';

    /**
     * Execute the console command.
     * This function loops over all customers and checks their installment and debt payments.
     * Sends WhatsApp notifications if payments are late.
     */
    public function handle(): int
    {
        // Get all customers
        $customers = Customer::all();

        // Check late payments for each customer
        foreach ($customers as $customer) {
            $this->checkInstallments($customer);
            $this->checkDebts($customer);
        }

        // Return success exit code
        return Command::SUCCESS;
    }

    /**
     * Check late installment payments for a given customer.
     * If no payment in last 30 days or no installment payments at all,
     * send WhatsApp reminder.
     */
    protected function checkInstallments($customer)
    {
        // Find the latest installment payment date related to customer's receipts
        $latestInstallmentPaymentDate = InstallmentPayment::whereHas('installment.receiptProduct.receipt', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
        ->whereHas('installment', function ($query) {
            $query->where('status', 1);  // Only active installments
        })
        ->latest('payment_date')
        ->value('payment_date');

        if ($latestInstallmentPaymentDate) {
            // Calculate days since last payment
            $paymentDate = Carbon::parse($latestInstallmentPaymentDate);
            $daysDiff = $paymentDate->diffInDays(Carbon::today());

            // If more than or equal 30 days, send reminder
            if ($daysDiff >= 30) {
                $lastDate = $paymentDate->format('Y-m-d');
                $msg = "السلام عليكم {$customer->name}، لم نقم بتسجيل أي دفعة أقساط منذ {$lastDate}. نذكرك بضرورة السداد لتفادي التأخير، وذلك لصالح محمد حمدان للإلكترونيات.";

                $customer->notify(new SendWhatsAppNotification($msg));
            }
        } else {
            // No installment payments found — check receipts without payments
            $receipts = Receipt::where('type', 0)
                ->where('customer_id', $customer->id)
                ->whereHas('receiptProducts.installment', function ($query) {
                    $query->where('status', 1)
                          ->whereDoesntHave('installmentPayments');
                })
                ->get();

            // Loop through receipts to check if any are older than 30 days
            foreach ($receipts as $receipt) {
                $paymentDate = Carbon::parse($receipt->receipt_date);
                $daysDiff = $paymentDate->diffInDays(Carbon::today());

                if ($daysDiff >= 30) {
                    $lastDate = $paymentDate->format('Y-m-d');
                    $msg = "السلام عليكم {$customer->name}، لم تقم بأي دفعة أقساط منذ تاريخ الفاتورة {$lastDate}. نذكرك بضرورة السداد لتفادي التأخير، وذلك لصالح محمد حمدان للإلكترونيات.";

                    $customer->notify(new SendWhatsAppNotification($msg));
                    break; // Send only one reminder per customer for installments
                }
            }
        }
    }

    /**
     * Check late debts for a given customer.
     * Sends WhatsApp reminder if debt payment is overdue by 30+ days.
     */
    protected function checkDebts($customer)
    {
        // Get all debts for customer with remaining unpaid amount
        $debts = Debt::where('customer_id', $customer->id)
                    ->where('remaining_debt', '>', 0)
                    ->get();

        // Loop through each debt to check latest payment date
        foreach ($debts as $debt) {
            // Get latest debt payment or fall back to debt date
            $latestDebtPayment = $debt->debtPayments()->latest('id')->first();
            $lastPaymentDate = $latestDebtPayment?->payment_date ?? $debt->debt_date;

            if ($lastPaymentDate) {
                $date = Carbon::parse($lastPaymentDate);
                $daysDiff = $date->diffInDays(Carbon::today());

                // If debt unpaid for 30+ days, send reminder
                if ($daysDiff >= 30) {
                    $formattedDate = $date->format('Y-m-d');
                    $msg ="السلام عليكم {$customer->name}، لديك دين لم يتم سداده منذ {$formattedDate}. نرجو السداد في أقرب وقت، وذلك لصالح محمد حمدان للإلكترونيات.";

                    $customer->notify(new SendWhatsAppNotification($msg));
                }
            }
        }
    }
}
