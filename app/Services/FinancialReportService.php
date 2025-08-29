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
    public function GetFinancialReport($data): array
    {
        try {
            // Parse dates only if موجودة، وإلا نرجع كل البيانات
            $startDate = $data['start_date'] ?? null;
            $endDate   = $data['end_date'] ?? null;

            // Collected installment payments
            $collectedInstallmentPayments = InstallmentPayment::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('payment_date', [$startDate, $endDate]);
            })->sum('amount');

            // Collected debt payments
            $collectedDebtPayments = DebtPayment::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('payment_date', [$startDate, $endDate]);
            })->sum('amount');

            // Financial transactions grouped by agent type & transaction type
            $transactions = FinancialTransaction::select(
                    'agents.type as agentType',
                    'financial_transactions.type as transactionType',
                    DB::raw('SUM(paid_amount) as totalPaid'),
                    DB::raw('SUM(GREATEST(0, total_amount - discount_amount - paid_amount)) as remainingDebt'),
                    DB::raw('SUM(total_amount) as totalAmount')
                )
                ->join('agents', 'agents.id', '=', 'financial_transactions.agent_id')
                ->when($startDate && $endDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
                ->where('agents.status', 0)
                ->groupBy('agents.type', 'financial_transactions.type')
                ->get();

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

                if (in_array($row->transactionType, [0, 1])) {
                    $financialTransactions[$fields['paymentField']] += (float) $row->totalPaid;
                }
                if ($row->transactionType == 0) {
                    $financialTransactions[$fields['debtField']] += (float) $row->remainingDebt;
                }
                if ($row->transactionType == 3) {
                    $financialTransactions[$fields['debtField']] += (float) $row->totalAmount;
                }
            }

            // Other calculations
            $totalExpenses = Payment::when($startDate && $endDate, fn($q) => $q->whereBetween('payment_date', [$startDate, $endDate]))->sum('amount');
            $totaldebt     = Debt::when($startDate && $endDate, fn($q) => $q->whereBetween('debt_date', [$startDate, $endDate]))->sum('remaining_debt');

            $totalInstallmentSalesValueInPeriod = Receipt::when($startDate && $endDate, fn($q) => $q->whereBetween('receipt_date', [$startDate, $endDate]))
                ->where('type', 0)
                ->sum('total_price');

            $totalCashSalesRevenue = Receipt::when($startDate && $endDate, fn($q) => $q->whereBetween('receipt_date', [$startDate, $endDate]))
                ->where('type', 1)
                ->sum('total_price');

            $totalRevenueFromSalesInPeriod = $totalCashSalesRevenue + $totalInstallmentSalesValueInPeriod;

            $cogsForPeriodSales = ReceiptProduct::when($startDate && $endDate, fn($q) =>
                    $q->whereHas('receipt', fn($r) => $r->whereBetween('receipt_date', [$startDate, $endDate])))
                ->sum(DB::raw('buying_price * quantity'));

            $firstpay = Installment::when($startDate && $endDate, fn($q) =>
                    $q->whereHas('receiptProduct.receipt', fn($r) => $r->whereBetween('receipt_date', [$startDate, $endDate])))
                ->sum('first_pay');

            $grossProfitFromSalesInPeriod = $totalRevenueFromSalesInPeriod - $cogsForPeriodSales;
            $adjustedCOGS = $totalInstallmentSalesValueInPeriod - $firstpay - $collectedInstallmentPayments;
            $operatingNetProfit = $grossProfitFromSalesInPeriod - $totalExpenses;

            return $this->successResponse(
                'Financial report retrieved successfully',
                200,
                [
                    'period' => [
                        'startDate' => $startDate ?? null,
                        'endDate'   => $endDate ?? null,
                    ],
                    'income_statement_summary' => [
                        'total_installment_sales_value_in_period' => (float) $totalInstallmentSalesValueInPeriod,
                        'total_revenue_from_sales_in_period'      => (float) $totalRevenueFromSalesInPeriod,
                        'total_expenses_in_period'                => (float) $totalExpenses,
                        'operating_net_profit_in_period'          => (float) $operatingNetProfit,
                        'adjustedCOGS'                            => (float) $adjustedCOGS,
                        'totaldebt'                               => (float) $totaldebt,
                        'collectedDebtPayments'                   => (float) $collectedDebtPayments,
                    ] + $financialTransactions,
                    'cash_flow_summary' => [
                        'cash_inflow_from_collected_installments' => (float) $collectedInstallmentPayments,
                    ],
                ]
            );

        } catch (Exception $e) {
            Log::error("Unexpected error in GetFinancialReport: " . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء استرجاع التقرير المالي، يرجى المحاولة مرة اخرى.');
        }
    }
}
