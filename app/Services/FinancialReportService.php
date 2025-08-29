<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\DebtPayment;
use App\Models\Installment;
use App\Models\ReceiptProduct;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;

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
            $endDate   = Carbon::parse($data['end_date'] ?? now())->toDateString();

            // Sum of all installment payments collected within the date range
            $collectedInstallmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            // Sum of all debt payments collected within the date range
            $collectedDebtPayments = DebtPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            /**
             * ------------------------------
             * 📌 Query financial transactions (single grouped query)
             * ------------------------------
             */
            $transactions = FinancialTransaction::select(
                    'agents.type as agentType',
                    'financial_transactions.type as transactionType',
                    DB::raw('SUM(paid_amount) as totalPaid'),
                    DB::raw('SUM(GREATEST(0, total_amount - discount_amount - paid_amount)) as remainingDebt'),
                    DB::raw('SUM(total_amount) as totalAmount')
                )
                ->join('agents', 'agents.id', '=', 'financial_transactions.agent_id')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('agents.status', 0)
                ->groupBy('agents.type', 'financial_transactions.type')
                ->get();

            /**
             * ------------------------------
             * 📌 Map results into final structure
             * ------------------------------
             */
            $map = [
                1 => ['paymentField' => 'collecteFinancialTransactionPaymentsDinar', 'debtField' => 'collecteFinancialTransactionDebtsDinar'],
                0 => ['paymentField' => 'collecteFinancialTransactionPaymentsDolar', 'debtField' => 'collecteFinancialTransactionDebtsDolar'],
            ];

            $financialTransactions = [
                'collecteFinancialTransactionPaymentsDinar' => 0,
                'collecteFinancialTransactionDebtsDinar'    => 0,
                'collecteFinancialTransactionPaymentsDolar' => 0,
                'collecteFinancialTransactionDebtsDolar'    => 0,
            ];

            foreach ($transactions as $row) {
                $fields = $map[$row->agentType] ?? null;
                if (!$fields) continue;

                // Payments (type = 0 or 1)
                if (in_array($row->transactionType, [0, 1])) {
                    $financialTransactions[$fields['paymentField']] += (int) $row->totalPaid;
                }

                // Debts (type = 0)
                if ($row->transactionType == 0) {
                    $financialTransactions[$fields['debtField']] += (int) $row->remainingDebt;
                }

                // Debts (type = 3)
                if ($row->transactionType == 3) {
                    $financialTransactions[$fields['debtField']] += (int) $row->totalAmount;
                }
            }

            /**
             * ------------------------------
             * 📌 Other calculations
             * ------------------------------
             */
            $totalExpenses = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $totaldebt     = Debt::whereBetween('debt_date', [$startDate, $endDate])->sum('remaining_debt');

            $totalInstallmentSalesValueInPeriod = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 0)
                ->sum('total_price');

            $totalCashSalesRevenue = Receipt::whereBetween('receipt_date', [$startDate, $endDate])
                ->where('type', 1)
                ->sum('total_price');

            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            $cogsForPeriodSales = ReceiptProduct::whereHas('receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum(DB::raw('buying_price * quantity'));

            $firstpay = Installment::whereHas('receiptProduct.receipt', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('receipt_date', [$startDate, $endDate]);
            })->sum('first_pay');

            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;

            $adjustedCOGS = $totalInstallmentSalesValueInPeriod - $firstpay - $collectedInstallmentPayments;

            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            /**
             * ------------------------------
             * 📌 Return response
             * ------------------------------
             */
            return $this->successResponse(
                'Financial report retrieved successfully',
                200,
                [
                    'period' => [
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ],
                    'income_statement_summary' => [
                        'total_installment_sales_value_in_period' => (int) $totalInstallmentSalesValueInPeriod,
                        'total_revenue_from_sales_in_period'      => (int) $totalRevenueFromSalesInPeriod,
                        'total_expenses_in_period'                => (int) $totalExpenses,
                        'operating_net_profit_in_period'          => (int) $operatingNetProfit,
                        'adjustedCOGS'                            => (int) $adjustedCOGS,
                        'totaldebt'                               => (int) $totaldebt,
                        'collectedDebtPayments'                   => (int) $collectedDebtPayments,
                    ] + $financialTransactions,

                    'cash_flow_summary' => [
                        'cash_inflow_from_collected_installments' => (int) $collectedInstallmentPayments,
                    ],
                ]
            );

        } catch (Exception $e) {
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء استرجاع التقرير المالي، يرجى المحاولة مرة اخرى.');
        }
    }
}
