<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Receipt;
use Illuminate\Console\Command;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendWhatsAppNotification;

class CheckLatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:late-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check invoices for late installment payments and send a message if overdue by a month.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $oneMonthAgo = $now->copy()->subMonth();

        $receiptsWithInstallments = Receipt::where('type', 'اقساط')->get();

        foreach ($receiptsWithInstallments as $receipt) {
            $latestPayment = InstallmentPayment::whereHas('installment.receiptProduct.receipt', function ($query) use ($receipt) {
                $query->where('id', $receipt->id);
            })
            ->orderByDesc('payment_date')
            ->first();

            if ($latestPayment) {
                $lastPaymentDate = Carbon::parse($latestPayment->payment_date);

                if ($lastPaymentDate->lt($oneMonthAgo)) {
                    $message = "تنبيه: تاريخ الفاتورة رقم {$receipt->receipt_number} هو {$receipt->receipt_date->format('Y-m-d')}، وتاريخ آخر دفعة هو {$lastPaymentDate->format('Y-m-d')}.";

                    if ($receipt->customer) {
                        Notification::send($receipt->customer, new SendWhatsAppNotification($message));
                        $this->info("✅ تم إرسال تنبيه WhatsApp إلى العميل رقم {$receipt->customer->id}.");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
