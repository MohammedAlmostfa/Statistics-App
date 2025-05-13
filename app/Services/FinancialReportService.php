<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Installment;
use App\Models\ReceiptProduct;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialReportService
{
    public function GetFinancialReport($data): array
    {
        try {
            // ضبط التواريخ بدون الوقت
            $startDate = Carbon::parse($data['start_date'] ?? Receipt::first()?->receipt_date ?? now())->toDateString();
            $endDate = Carbon::parse($data['end_date'] ?? now())->toDateString();

            // مجموع الدفعات المستلمة من الأقساط
            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // إجمالي المصاريف خلال الفترة
            $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // إجمالي قيمة مبيعات الأقساط خلال الفترة
            $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 0) // استخدام القيمة الصحيحة كعدد صحيح
                ->sum('total_price');

            $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 1)
                ->sum('total_price');

            // حساب إجمالي الإيرادات
            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            // حساب تكلفة البضائع المباعة (COGS)
            $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum(DB::raw('buying_price * quantity'));

            // حساب الدفعات الأولية المستلمة للأقساط
            $firstpay = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum('first_pay');

            // حساب إجمالي الربح
            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;

            // تصحيح `cogsForPeriodSales` لضمان دقة الحسابات
            $adjustedCOGS = $totalCashSalesRevenue - $firstpay - $collectedInstallmentPayments;

            // حساب صافي الربح التشغيلي
            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            return $this->successResponse(
                'Financial report retrieved successfully',
                200,
                [
                    'period' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                    'income_statement_summary' => [
                        'total_installment_sales_value_in_period' => (int) $totalInstallmentSalesValueInPeriod,
                        'total_revenue_from_sales_in_period' => (int) $totalRevenueFromSalesInPeriod,
                        'total_expenses_in_period' => (int) $totalExpenses,
                        'operating_net_profit_in_period' => (int) $operatingNetProfit,
                        'adjustedCOGS'=>(int) $adjustedCOGS
                    ],
                    'cash_flow_summary' => [
                        'cash_inflow_from_collected_installments' => (int) $collectedInstallmentPayments,
                    ],
                ]
            );

        } catch (Exception $e) {
            // تسجيل الخطأ للحصول على تفاصيل المشكلة
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء توليد التقرير المالي، يرجى المحاولة لاحقًا.');
        }
    }

    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
