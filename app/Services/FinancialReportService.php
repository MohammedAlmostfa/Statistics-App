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


            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');


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

            $firstpay = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum('first_pay');

            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;
            $cogsForPeriodSales =  $cogsForPeriodSales -     $firstpay -  $collectedInstallmentPayments ;

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
                    ],
                    'cash_flow_summary' => [

                        'cash_inflow_from_collected_installments' => (int) $collectedInstallmentPayments,

                    ],

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
