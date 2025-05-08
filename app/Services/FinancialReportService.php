<?php

namespace App\Services;

use Exception;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ReceiptProduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * خدمة لإدارة التقارير المالية.
 * توفر طرقًا للتعامل مع تجميع البيانات المالية مع معالجة الأخطاء.
 */
class FinancialReportService
{
    /**
     * استرجاع تقرير مالي مفصل لفترة معينة.
     *
     * @param array $data مصفوفة تحتوي على 'startDate' و 'endDate'.
     * @return array استجابة منظمة تحتوي على بيانات التقرير أو رسالة خطأ.
     */
    public function GetFinancialReport($data)
    {
        try {


            $startDate = $data["start_date"]?? Receipt::first()?->receipt_date ?? now();


            $endDate = $data["end_date"]??now();

            // إجمالي المصروفات النقدية في الفترة المحددة
            $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // إجمالي الإيرادات من المبيعات النقدية خلال الفترة
            $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 'نقدي')
                ->sum('total_price');

            // إجمالي قيمة مبيعات الأقساط خلال الفترة
            $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 'اقساط')
                ->sum('total_price');

            // مجموع الدفعات الأولى للأقساط التي تم بيعها خلال الفترة
            $firstPaymentsFromPeriodSales = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate])
                    ->where('type', 'اقساط');
            })->sum('first_pay');

            // مجموع الأقساط التي تم تحصيلها نقداً خلال الفترة
            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // تكلفة البضاعة المباعة خلال الفترة (المشتريات × الكمية)
            $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum(DB::raw('buying_price * quantity'));

            // حساب ملخص الأرباح:
            // الإيرادات = النقدي + الأقساط
            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            // الربح الإجمالي = الإيرادات - تكلفة البضاعة المباعة
            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;

            // صافي الربح التشغيلي = الربح الإجمالي - المصروفات
            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            // ملخص التدفقات النقدية:
            // مجموع الداخل النقدي من (المبيعات النقدية + الدفعات الأولى + الأقساط المحصلة)
            $totalCashInflowInPeriod = $totalCashSalesRevenue + $firstPaymentsFromPeriodSales + $collectedInstallmentPayments;

            // صافي التدفق النقدي = الداخل - المصروفات
            $netCashFlowInPeriod = $totalCashInflowInPeriod - $totalExpenses;

            // ملخص الديون حتى نهاية الفترة:

            // إجمالي قيمة مبيعات الأقساط حتى نهاية الفترة
            $allTimeTotalInstallmentSalesValue = Receipt::where('type', 'اقساط')
                ->whereDate('receipt_date', '<=', $endDate)
                ->sum('total_price');

            // مجموع الدفعات الأولى حتى نهاية الفترة
            $allTimeTotalFirstPayments = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->where('type', 'اقساط')
                    ->whereDate('receipt_date', '<=', $endDate);
            })->sum('first_pay');

            // مجموع الأقساط المحصلة حتى نهاية الفترة
            $allTimeTotalCollectedInstallmentPayments = InstallmentPayment::whereDate('payment_date', '<=', $endDate)->sum('amount');

            // مجموع ما تم تحصيله فعليًا من مبيعات الأقساط (دفعة أولى + أقساط محصلة)
            $allTimeTotalCollectedOnInstallments = $allTimeTotalFirstPayments + $allTimeTotalCollectedInstallmentPayments;

            // إجمالي الديون المتبقية = إجمالي مبيعات الأقساط - مجموع ما تم تحصيله
            $totalOutstandingDebtsAsOfEndDate = $allTimeTotalInstallmentSalesValue - $allTimeTotalCollectedOnInstallments;

            // إرجاع البيانات بصيغة منظمة
            return [
                'status' => 200,
                'message'=>'تم استرجاع التقرير بنجاخ',
                'data' => [
                    'period' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                    'income_statement_summary' => [
                        'total_cash_sales_revenue' => (int)$totalCashSalesRevenue,
                        'total_installment_sales_value_in_period' => (int)$totalInstallmentSalesValueInPeriod,
                        'total_revenue_from_sales_in_period' => (int)$totalRevenueFromSalesInPeriod,
                        'cogs_for_period_sales' => (int)$cogsForPeriodSales,
                        'gross_profit_from_sales_in_period' => (int)$grossProfitFromSalesInPeriod,
                        'total_expenses_in_period' => (int)$totalExpenses,
                        'operating_net_profit_in_period' => (int)$operatingNetProfit,
                    ],
                    'cash_flow_summary' => [
                        'cash_inflow_from_cash_sales' => (int)$totalCashSalesRevenue,
                        'cash_inflow_from_first_payments_new_installments' =>(int) $firstPaymentsFromPeriodSales,
                        'cash_inflow_from_collected_installments' => $collectedInstallmentPayments,
                        'total_cash_inflow_in_period' => (int)$totalCashInflowInPeriod,
                        'total_cash_outflow_expenses_in_period' => (int)$totalExpenses,
                        'net_cash_flow_in_period' => (int)$netCashFlowInPeriod,
                    ],
                    'debt_summary' => [
                        'installment_sales_value_up_to_end_date' => (int)$allTimeTotalInstallmentSalesValue,
                        'first_payments_up_to_end_date' => (int)$allTimeTotalFirstPayments,
                        'collected_installments_up_to_end_date' => (int)$allTimeTotalCollectedInstallmentPayments,
                        'collected_on_installments_up_to_end_date' =>(int) $allTimeTotalCollectedOnInstallments,
                        'outstanding_debts_as_of_end_date' => (int)$totalOutstandingDebtsAsOfEndDate,
                    ]
                ]
            ];
        } catch (Exception $e) {
            // تسجيل الخطأ في السجل في حال حدوث استثناء غير متوقع
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب التقرير يرجى المحاولة مرة أخرى.',
            ];

        }
    }
}
