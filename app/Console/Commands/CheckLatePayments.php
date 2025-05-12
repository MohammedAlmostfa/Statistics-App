<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Receipt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendWhatsAppNotification;

class CheckLatePayments extends Command
{
    protected $signature = 'check:late-payments';
    protected $description = 'Check invoices for late installment payments and send reminders to customers if overdue.';

    public function handle(): int
    {
        $now = Carbon::now();


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

                if ($latestPayment) {
                    $lastPaymentDate = Carbon::parse($latestPayment->payment_date)->format('Y-m-d');
                    $paymentMessage = " آخر دفعة كانت بتاريخ {$lastPaymentDate}.";
                } else {
                    $paymentMessage = " لم يتم سداد أي دفعة حتى الآن.";
                }

                $productName = $product->product->name ?? 'منتج غير محدد';
                $customerName = $receipt->customer->name;
                $receiptNumber = $receipt->receipt_number;
                $receiptDate = $receipt->receipt_date->format('Y-m-d');
                $installmentAmount = $installment->installment;


                $message = "مرحبًا {$customerName}، نود تذكيرك بأن القسط الخاص بالمنتج '{$productName}' في الفاتورة رقم {$receiptNumber} (بتاريخ: {$receiptDate}) قد تجاوز موعد السداد.";
                $message .= "\n{$paymentMessage} مبلغ القسط المترتب عليك الآن هو {$installmentAmount} ريال.";
                $message .= "\nنرجو منك تسديد القسط في أقرب وقت ممكن لتجنب أي تأخير إضافي والحفاظ على التزامك.";
                $message .= "\nإذا كنت بحاجة إلى أي مساعدة أو لديك استفسارات، لا تتردد في التواصل معنا، فنحن هنا لخدمتك.";
                $message .= "\n📌 **معرض محمد حمدان **";

                if ($receipt->customer) {
                    Notification::send($receipt->customer, new SendWhatsAppNotification($message));
                    $this->info("✅ تم إرسال تذكير بالسداد إلى العميل {$customerName} بخصوص المنتج '{$productName}'.");
                }
            }
        }

        return Command::SUCCESS;
    }
}
