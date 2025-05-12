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

/**
 * FinancialReportService
 *
 * This service provides methods to generate detailed financial reports
 * for a specified period. It calculates income, expenses, profit,
 * cash flow, and outstanding debts.
 */
class FinancialReportService
{
    /**
     * Generate a detailed financial report for a given date range.
     *
     * @param array $data Should contain 'start_date' and 'end_date'.
     * @return array Standard response with financial report or error message.
     */
    public function GetFinancialReport($data): array
    {
        try {
            // Get the start and end dates, fallback to earliest receipt or current date.
            $startDate = Carbon::parse($data['start_date'] ?? Receipt::first()?->receipt_date ?? now())->toDateString();
            $endDate = Carbon::parse($data['end_date'] ?? now())->toDateString();


            // // Total expenses during the period
            // $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // // Revenue from cash sales
            // $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
            //     ->where('type', 'نقدي')
            //     ->sum('total_price');

            // // Value of installment sales during the period
            // $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
            //     ->where('type', 'اقساط')
            //     ->sum('total_price');

            // // First payments collected for new installment sales
            // $firstPaymentsFromPeriodSales = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
            //     $query->whereBetween('receipt_date', [$startDate, $endDate])->where('type', 'اقساط');
            // })->sum('first_pay');

            // // Installment payments collected during the period
            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // // Cost of goods sold = buying_price * quantity
            // $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
            //     $query->whereBetween('receipt_date', [$startDate, $endDate]);
            // })->sum(DB::raw('buying_price * quantity'));

            // // Total revenue = cash sales + installment sales
            // $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            // // Gross profit = total revenue - COGS
            // $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;

            // // Operating net profit = gross profit - expenses
            // $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            // // Total cash inflow
            // $totalCashInflowInPeriod = $totalCashSalesRevenue + $firstPaymentsFromPeriodSales + $collectedInstallmentPayments;

            // // Net cash flow = inflow - outflow
            // $netCashFlowInPeriod = $totalCashInflowInPeriod - $totalExpenses;

            // // Total installment sales up to end date
            // $allTimeTotalInstallmentSalesValue = Receipt::where('type', 'اقساط')
            //     ->whereDate('receipt_date', '<=', $endDate)
            //     ->sum('total_price');

            // // All first payments received up to end date
            // $allTimeTotalFirstPayments = Installment::whereHas('receiptProduct.receipt', function ($query) use ($endDate) {
            //     $query->where('type', 'اقساط')->whereDate('receipt_date', '<=', $endDate);
            // })->sum('first_pay');

            // // All collected installment payments up to end date
            // $allTimeTotalCollectedInstallmentPayments = InstallmentPayment::whereDate('payment_date', '<=', $endDate)->sum('amount');

            // // Total collected = first payments + installment payments
            // $allTimeTotalCollectedOnInstallments = $allTimeTotalFirstPayments + $allTimeTotalCollectedInstallmentPayments;

            // // Outstanding debts = total sales - collected amount
            // $totalOutstandingDebtsAsOfEndDate = $allTimeTotalInstallmentSalesValue - $allTimeTotalCollectedOnInstallments;
            $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');


            $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', '0')
                ->sum('total_price');


            $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', '1')
                ->sum('total_price');

            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum(DB::raw('buying_price * quantity'));


            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;


            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            Log::info("Financial Report Calculation: Start Date: $startDate, End Date: $endDate");
            Log::info("Total Installment Sales Value: $totalInstallmentSalesValueInPeriod");
            Log::info("Total Revenue from Sales: $totalRevenueFromSalesInPeriod");
            Log::info("Total Expenses: $totalExpenses");
            Log::info("Operating Net Profit: $operatingNetProfit");
            Log::info("grossProfitFromSalesInPeriod: $grossProfitFromSalesInPeriod");
            return $this->successResponse(
                'Financial report retrieved successfully',
                200,
                [
                    'period' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                    'income_statement_summary' => [
                        //'total_cash_sales_revenue' => (int) $totalCashSalesRevenue,
                        'total_installment_sales_value_in_period' => (int) $totalInstallmentSalesValueInPeriod,
                        'total_revenue_from_sales_in_period' => (int) $totalRevenueFromSalesInPeriod,
                        //'cogs_for_period_sales' => (int) $cogsForPeriodSales,
                       // 'gross_profit_from_sales_in_period' => (int) $grossProfitFromSalesInPeriod,
                        'total_expenses_in_period' => (int) $totalExpenses,
                        'operating_net_profit_in_period' => (int) $operatingNetProfit,
                    ],
                    'cash_flow_summary' => [
                        //'cash_inflow_from_cash_sales' => (int) $totalCashSalesRevenue,
                        //'cash_inflow_from_first_payments_new_installments' => (int) $firstPaymentsFromPeriodSales,
                        'cash_inflow_from_collected_installments' => (int) $collectedInstallmentPayments,
                        //'total_cash_inflow_in_period' => (int) $totalCashInflowInPeriod,
                       // 'total_cash_outflow_expenses_in_period' => (int) $totalExpenses,
                        //'net_cash_flow_in_period' => (int) $netCashFlowInPeriod,
                    ],
                    'debt_summary' => [
                        //'installment_sales_value_up_to_end_date' => (int) $allTimeTotalInstallmentSalesValue,
                        //'first_payments_up_to_end_date' => (int) $allTimeTotalFirstPayments,
                       // 'collected_installments_up_to_end_date' => (int) $allTimeTotalCollectedInstallmentPayments,
                       // 'collected_on_installments_up_to_end_date' => (int) $allTimeTotalCollectedOnInstallments,
                       // 'outstanding_debts_as_of_end_date' => (int) $totalOutstandingDebtsAsOfEndDate,
                    ]
                ]
            );

        } catch (Exception $e) {
            // Log unexpected errors and return a user-friendly message
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return $this->errorResponse('An error occurred while generating the report. Please try again later.');
        }
    }

    /**
     * Return a standardized success response.
     *
     * @param string $message
     * @param int $status
     * @param mixed|null $data
     * @return array
     */
    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    /**
     * Return a standardized error response.
     *
     * @param string $message
     * @param int $status
     * @return array
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
