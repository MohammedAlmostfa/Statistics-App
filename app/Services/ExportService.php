<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class ExportService
{
    /**
     * جلب مجموع المدفوعات والمعاملات المالية بالدينار والدولار
     *
     * @param string|null $date
     * @return array
     */
    public function getAllExports(?string $date = null): array
    {
        try {


            $totalPaidPayment = Payment::when($date, fn($query) => $query->whereDate('payment_date', $date))
                ->sum('amount');


            $totalFinancialTransactionDinar = FinancialTransaction::whereHas('agent', fn($query) => $query->where('type', 1))
                ->whereIn('type', [0, 1])
                ->whereNotNull('paid_amount')
                ->when($date, fn($query) => $query->whereDate('transaction_date', $date))
                ->sum('paid_amount');


            $totalFinancialTransactionDolar = FinancialTransaction::whereHas('agent', fn($query) => $query->where('type', 0))
                ->whereIn('type', [0, 1])
                ->whereNotNull('paid_amount')
                ->when($date, fn($query) => $query->whereDate('transaction_date', $date))
                ->sum('paid_amount');

            return [
                'status' => 200,
                'message' => 'تم جلب البيانات بنجاح.',
                'data' => [
                    'totalPaidPayment' => (float)$totalPaidPayment,
                    'totalFinancialTransactionDinar' => (float)$totalFinancialTransactionDinar,
                    'totalFinancialTransactionDolar' => (float)$totalFinancialTransactionDolar,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Error fetching export data: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب بيانات الصارات.',
                'data' => [],
            ];
        }
    }
}
