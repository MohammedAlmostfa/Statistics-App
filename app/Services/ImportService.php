<?php

namespace App\Services;

use App\Models\Receipt;
use App\Models\InstallmentPayment;
use App\Models\DebtPayment;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class ImportService
 *
 * This service handles the retrieval and aggregation of all import-related data,
 * including cash receipts, installment receipts, installment payments, and debt payments.
 * It supports filtering by date and returns total amounts and transaction counts.
 */
class ImportService
{
    /**
     * Retrieve all import transactions (receipts, payments, etc.)
     *
     * This method fetches and aggregates:
     * - Cash receipts
     * - Installment receipts (sum of first payments for all installment products)
     * - Installment payments
     * - Debt payments
     *
     * It can filter data by a specific date if provided.
     *
     * @param array $data ['date' => 'YYYY-MM-DD' (optional)]
     * @return array
     */
    public function getAllImports($data): array
    {
        try {
            $date = $data['date'] ?? null;

            /** -----------------------------------------
             *  Cash Receipts (الإيصالات النقدية)
             * ----------------------------------------- */
            $cashReceipts = Receipt::with('user:id,name')
                ->where('type', 'نقدي')
                ->when($date, fn($query) => $query->whereDate('receipt_date', $date))
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($r) => [
                    'amount' => $r->total_price ?? 0,
                    'date'   => $r->created_at->format('Y-m-d'),
                    'user'   => $r->user->name ?? 'غير معروف',
                    'type'   => 'نقدي',
                ]);

            /** -----------------------------------------
             *  Installment Receipts (الإيصالات بالأقساط)
             * Each product may have its own installment plan.
             * We sum all first payments (first_pay) for each receipt.
             * ----------------------------------------- */
            $installmentReceipts = Receipt::with(['user:id,name', 'receiptProducts.installment'])
                ->where('type', 'اقساط')
                ->when($date, fn($query) => $query->whereDate('receipt_date', $date))
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($r) => [
                    'amount' => $r->receiptProducts->sum(fn($product) => $product->installment->first_pay ?? 0),
                    'date'   => $r->created_at->format('Y-m-d'),
                    'user'   => $r->user->name ?? 'غير معروف',
                    'type'   => 'قسط',
                ]);

            /** -----------------------------------------
             *  Installment Payments (دفعات الأقساط)
             * ----------------------------------------- */
            $installmentPayments = InstallmentPayment::with('user:id,name')
                ->when($date, fn($q) => $q->whereDate('payment_date', $date))
                ->orderByDesc('payment_date')
                ->get()
                ->map(fn($p) => [
                    'amount' => $p->amount ?? 0,
                    'date'   => $p->payment_date->format('Y-m-d'),
                    'user'   => $p->user->name ?? 'غير معروف',
                    'type'   => 'قسط دين',
                ]);

            /** -----------------------------------------
             *  Debt Payments (تسديد الديون)
             * ----------------------------------------- */
            $debtPayments = DebtPayment::with('user:id,name')
                ->when($date, fn($q) => $q->whereDate('payment_date', $date))
                ->orderByDesc('payment_date')
                ->get()
                ->map(fn($p) => [
                    'amount' => $p->amount ?? 0,
                    'date'   => $p->payment_date->format('Y-m-d'),
                    'user'   => $p->user->name ?? 'غير معروف',
                    'type'   => 'تسديد دين',
                ]);

            /** -----------------------------------------
             *  Merge All Collections
             * ----------------------------------------- */
            $merged = collect($cashReceipts, $installmentReceipts, $installmentPayments, $debtPayments)
                ->values();

            $totalAmount = $merged->sum('amount');

            /** -----------------------------------------
             *  Successful Response
             * ----------------------------------------- */
            return [
                'status' => 200,
                'message' => 'تم جلب بيانات الواردات بنجاح.',
                'data' => [
                    'total_amount' => $totalAmount,
                    'imports' =>    $merged,
                ]



            ];
        } catch (Exception $e) {
            Log::error('Error fetching import data: ' . $e->getMessage());

            /** -----------------------------------------
             *  Error Response
             * ----------------------------------------- */
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب بيانات الواردات.',
                'data' => [],
            ];
        }
    }
}
