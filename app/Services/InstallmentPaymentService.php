<?php

namespace App\Services;

use Exception;
use App\Models\Debt;
use App\Models\Receipt;
use App\Models\Installment;
use App\Models\ActivitiesLog;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\InstallmentPaidEvent;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service class to handle installment payments including create, update, and delete operations.
 * All actions are logged and wrapped in database transactions to ensure consistency.
 */
class InstallmentPaymentService extends Service
{
    /**
     * Create a new installment payment.
     *
     * @param array $data Data containing 'payment_date' and 'amount'.
     * @param int $id The installment ID.
     * @return array Structured success or error response in Arabic.
     */
    public function createInstallmentPayment(array $data, $id): array
    {
        DB::beginTransaction();

        try {
            $installment = Installment::findOrFail($id);

            $installmentPayment = $installment->installmentPayments()->create([
                'payment_date' => $data['payment_date'],
                'amount'       => $data['amount'],
            ]);

            ActivitiesLog::create([
                'user_id'     => Auth::id(),
                'description' => 'تم تحصيل مبلغ قدره ' . $data['amount'] . ' من العميل ' . $installment->receiptProduct->receipt->customer->name,
                'type_id'     => $installmentPayment->id,
                'type_type'   => InstallmentPayment::class,
            ]);
            event(new InstallmentPaidEvent($installment));

            DB::commit();

            return $this->successResponse('تم تسجيل دفعة القسط بنجاح', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating installment payment: ' . $e->getMessage());

            return $this->errorResponse('حدث خطأ أثناء تسديد القسط، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Update an existing installment payment.
     *
     * @param array $data Data containing the new 'amount'.
     * @param int $id The installment payment ID.
     * @return array Structured success or error response in Arabic.
     */
    public function updateInstallmentPayment(array $data, $id): array
    {
        DB::beginTransaction();

        try {
            $installmentPayment = InstallmentPayment::findOrFail($id);

            ActivitiesLog::create([
                'user_id'     => Auth::id(),
                'description' => 'تم تعديل المبلغ المحصل من ' . $installmentPayment->amount . ' إلى ' . $data['amount'] . ' من العميل ' . $installmentPayment->installment->receipt->customer->name,
                'type_id'     => $installmentPayment->id,
                'type_type'   => InstallmentPayment::class,
            ]);

            $installmentPayment->update([
                'amount' => $data['amount'],
            ]);

            DB::commit();

            return $this->successResponse('تم تحديث دفعة القسط بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating installment payment: ' . $e->getMessage());

            return $this->errorResponse('حدث خطأ أثناء تحديث دفعة القسط، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Delete an installment payment.
     *
     * @param InstallmentPayment $installmentPayment The payment instance to delete.
     * @return array Structured success or error response in Arabic.
     */
    public function deleteInstallmentPayment(InstallmentPayment $installmentPayment): array
    {
        DB::beginTransaction();

        try {

            ActivitiesLog::create([
                'user_id'     => Auth::id(),
                'description' => 'تم حذف دفعة قسط بمبلغ ' . $installmentPayment->amount . ' من العميل ' . $installmentPayment->installment->receiptProduct->receipt->customer->name,
                'type_id'     => $installmentPayment->id,
                'type_type'   => InstallmentPayment::class,
            ]);

            $installment = $installmentPayment->installment;
            if ($installment->status === 'مسدد') {
                $installment->status = 'قيد التسديد';
                $installment->save();
            }
            $installmentPayment->delete();

            DB::commit();

            return $this->successResponse('تم حذف دفعة القسط بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting installment payment: ' . $e->getMessage());

            return $this->errorResponse('حدث خطأ أثناء حذف دفعة القسط، يرجى المحاولة مرة أخرى.');
        }
    }


    /**
     * Process installment payment for a specific receipt.
     *
     * @param array $data - Contains the payment amount.
     * @param int $id - The receipt ID for which the installment payment is being made.
     * @return JsonResponse - A response indicating success or an error.
     */
    public function installmentPaymentReceipt($data, $id)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // جلب فواتير الأقساط
            $allreceipt = Receipt::with([
                'receiptProducts' => function ($query) {
                    $query->select('id', 'receipt_id', 'product_id', 'selling_price', 'quantity');
                },
                'receiptProducts.installment' => function ($query) {
                    $query->select('id', 'receipt_product_id');
                },
                'receiptProducts.installment.InstallmentPayments' => function ($query) {
                    $query->select('id', 'installment_id', 'amount');
                },
            ])
            ->where('customer_id', $id)
            ->where('type', 'اقساط')
            ->get();

            // جلب الديون العادية
            $adddebts = Debt::with(['debtPayments'])->where('customer_id', $id)->get();

            // حساب المتبقي من الأقساط
            $installmentItems = [];
            $totalRemainingInstallments = 0;

            foreach ($allreceipt as $receipt) {
                foreach ($receipt->receiptProducts as $product) {
                    $installment = $product->installment;
                    if ($installment) {
                        $totalPrice = $product->selling_price * $product->quantity;
                        $paid = $installment->installmentPayments->sum('amount');
                        $remaining = max(0, $totalPrice - $paid);

                        if ($remaining > 0) {
                            $product->remaining_price = $remaining;
                            $installmentItems[] = $product;
                            $totalRemainingInstallments += $remaining;
                        }
                    }
                }
            }

            // حساب المتبقي من الديون
            $debtItems = [];
            $totalRemainingDebt = 0;

            foreach ($adddebts as $debt) {
                $paid = $debt->debtPayments->sum('amount');
                $remaining = max(0, $debt->payment_amount - $paid);

                if ($remaining > 0) {
                    $debt->calculated_remaining = $remaining;
                    $debtItems[] = $debt;
                    $totalRemainingDebt += $remaining;
                }
            }

            $totalOutstanding = $totalRemainingInstallments + $totalRemainingDebt;

            if ($totalOutstanding == 0) {
                return $this->errorResponse('لا يوجد دفعات متبقية للتسديد.', 400);
            }

            $amountToDistribute = $data['amount'];
            $installmentShare = round(($totalRemainingInstallments / $totalOutstanding) * $amountToDistribute, 2);
            $debtShare = $amountToDistribute - $installmentShare;

            // توزيع الأقساط بدقة
            $remainingInstallmentPayment = $installmentShare;
            usort($installmentItems, fn ($a, $b) => $b->remaining_price <=> $a->remaining_price);

            foreach ($installmentItems as $index => $product) {
                $installment = $product->installment;
                $isLast = $index === count($installmentItems) - 1;

                $maxToPay = min($product->remaining_price, $remainingInstallmentPayment);
                $actualPayment = $isLast ? $remainingInstallmentPayment : $maxToPay;

                if ($actualPayment > 0) {
                    $installment->installmentPayments()->create([
                        'payment_date' => now(),
                        'amount' => $actualPayment,
                        'user_id' => $userId,
                    ]);

                    $remainingInstallmentPayment -= $actualPayment;
                }
            }

            // توزيع الديون بدقة
            $remainingDebtPayment = $debtShare;
            usort($debtItems, fn ($a, $b) => $b->calculated_remaining <=> $a->calculated_remaining);

            foreach ($debtItems as $index => $debt) {
                $isLast = $index === count($debtItems) - 1;

                $maxToPay = min($debt->calculated_remaining, $remainingDebtPayment);
                $actualPayment = $isLast ? $remainingDebtPayment : $maxToPay;

                if ($actualPayment > 0) {
                    $debt->debtPayments()->create([
                        'user_id' => $userId,
                        'payment_date' => now(),
                        'amount' => $actualPayment,
                    ]);

                    $remainingDebtPayment -= $actualPayment;
                }
            }

            DB::commit();
            return $this->successResponse('تم دفع الأقساط والديون بنجاح!', 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء معالجة دفعة القسط: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء الدفع، يرجى إعادة المحاولة لاحقاً.', 500);
        }
    }


}
