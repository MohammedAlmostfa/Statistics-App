<?php

namespace App\Services;

use App\Models\Debt;
use Exception;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\DebtPayment;
use App\Models\Installment;
use App\Models\ReceiptProduct;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialReportService extends Service
{
    /**
     * Generate a financial report for the given date range.
     *
     * @param array $data Associative array containing 'start_date' and 'end_date' keys (optional).
     * @return array Structured response containing the report data or an error message.
     */
    public function GetFinancialReport($data): array
    {
        try {
            // Parse and format the start and end dates (defaults to earliest receipt or today)
            $startDate = Carbon::parse($data['start_date'] ?? Receipt::first()?->receipt_date ?? now())->toDateString();
            $endDate = Carbon::parse($data['end_date'] ?? now())->toDateString();

            // Sum of all installment payments collected within the date range
            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $collectedDebtPayments =DebtPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // Total expenses recorded within the date range
            $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $totaldebt=Debt::whereBetween('debt_date', [$startDate, $endDate])->sum('remaining_debt');

            // Total value of installment-based sales (type 0) in the period
            $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 0)
                ->sum('total_price');

            // Total cash-based sales (type 1) in the period
            $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 1)
                ->sum('total_price');

            // Total revenue from both cash and installment sales
            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            // Cost of goods sold (COGS) based on the purchase price of sold products
            $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum(DB::raw('buying_price * quantity'));

            // Total of first payments received on installment purchases
            $firstpay = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum('first_pay');

            // Gross profit = Revenue - COGS
            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;

            // Adjusted cost of goods sold for installment-specific analysis
            $adjustedCOGS = $totalInstallmentSalesValueInPeriod - $firstpay - $collectedInstallmentPayments;

            // Operating net profit = Gross profit - Expenses
            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            // Return a successful structured financial report
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
                        'adjustedCOGS' => (int) $adjustedCOGS,
                        'totaldebt' => (int) $totaldebt,
                        'collectedDebtPayments' => (int) $collectedDebtPayments,
                    ],
                    'cash_flow_summary' => [
                        'cash_inflow_from_collected_installments' => (int) $collectedInstallmentPayments,
                    ],
                ]
            );

        } catch (Exception $e) {
            // Log the error for debugging
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء استرجاع التقرير المالي، يرجى المحاولة مرة اخرى.');
        }
    }

}
