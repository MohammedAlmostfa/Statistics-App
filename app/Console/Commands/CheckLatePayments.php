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
    protected $signature = 'check:late-payments';
    protected $description = 'Check invoices for late installment payments and send a message if overdue by a month.';

    public function handle(): int
    {
        $now = Carbon::now();
        $oneMonthAgo = $now->copy()->subMonth();

        $receipts = Receipt::where('type', 'اقساط')
            ->with(['receiptProducts.installment.InstallmentPayments', 'receiptProducts.product', 'customer'])
            ->get();

        foreach ($receipts as $receipt) {
            foreach ($receipt->receiptProducts as $product) {
                $installment = $product->installment;

                if (!$installment || $installment->status !== 'قيد التسديد') {
                    continue;
                }

                $latestPayment = $installment->InstallmentPayments->sortByDesc('payment_date')->first();

                if ($latestPayment && Carbon::parse($latestPayment->payment_date)->lt($oneMonthAgo)) {
                    $lastPaymentDate = Carbon::parse($latestPayment->payment_date)->format('Y-m-d');
                    $productName = $product->description ?: ($product->product->name ?? 'منتج غير محدد');
                    $customerName = $receipt->customer->name ;

                    $message = "تنبيه: القسط الخاص بالمنتج '{$productName}' في الفاتورة رقم {$receipt->receipt_number} (تاريخها: {$receipt->receipt_date->format('Y-m-d')}) ";
                    $message .= "التابع للعميل {$customerName} متأخر، حيث أن آخر دفعة كانت بتاريخ {$lastPaymentDate}. يرجى المبادرة بالسداد.";

                    if ($receipt->customer) {
                        Notification::send($receipt->customer, new SendWhatsAppNotification($message));
                        $this->info("✅ تم إرسال تنبيه WhatsApp إلى العميل {$customerName} بخصوص المنتج '{$productName}'.");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
