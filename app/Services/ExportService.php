<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class ExportService
{
    public function getAllExports()
    {
        try {
            // مجموع جميع المدفوعات
            $totalPaidPayment = Payment::sum('amount');

            // مجموع المعاملات المالية بالدينار (agent.type = 1) وأنواع معينة
            $totalFinancialTransactionDinar = FinancialTransaction::whereHas('agent', function($query){
                $query->where('type', '1');
            })->whereIn('type', ["0","1"])
              ->whereNotNull('paid_amount')
              ->sum('paid_amount');

            // مجموع المعاملات المالية بالدولار (agent.type = 0) وأنواع معينة
            $totalFinancialTransactionDolar = FinancialTransaction::whereHas('agent', function($query){
                $query->where('type', '0');
            })->whereIn('type', ["0","1"])
              ->whereNotNull('paid_amount')
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
