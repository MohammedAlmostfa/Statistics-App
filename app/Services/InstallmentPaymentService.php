<?php

namespace App\Services;

use Exception;
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
        DB::beginTransaction(); // Start transaction to ensure safe execution.

        try {
            // Fetch the receipt along with related products, installments, and payments.
            $receipt = Receipt::with([
                'receiptProducts' => function ($query) {
                    $query->select('id', 'receipt_id', 'product_id', 'selling_price', 'quantity');
                },
                'receiptProducts.installment' => function ($query) {
                    $query->select('id', 'receipt_product_id');
                },
                'receiptProducts.installment.InstallmentPayments' => function ($query) {
                    $query->select('id', 'installment_id', 'amount');
                },
            ])->findOrFail($id);

            $totalRemainingDebt = 0; // Store the total remaining debt for the receipt.
            $totalPaidForReceipt = 0; // Store the total previous payments for the receipt.

            // Calculate total payments and remaining balance for each product.
            foreach ($receipt->receiptProducts as $product) {
                $installment = $product->installment;

                if ($installment) {
                    // Calculate the total price for the product.
                    $totalPrice = $product->selling_price * $product->quantity;

                    // Calculate the total payments made for this installment.
                    $totalPaidForProductInstallment = $installment->installmentPayments()->sum('amount');

                    // Calculate the remaining amount for this product.
                    $remainingPrice = $totalPrice - $totalPaidForProductInstallment;

                    // Ensure the remaining amount is not negative.
                    $product->remaining_price = max(0, $remainingPrice);

                    // Accumulate total remaining debt for the receipt.
                    $totalRemainingDebt += $product->remaining_price;
                    $totalPaidForReceipt += $totalPaidForProductInstallment;
                }
            }

            // Validate if there's any pending amount to be paid.
            if ($totalRemainingDebt <= 0) {
                return $this->errorResponse('لا يوجد مبلغ مستحق للدفع لهذه الفاتورة.', 400);
            }

            // Validate the payment amount entered.
            if ($data['amount'] <= 0) {
                return $this->errorResponse('يجب أن يكون مبلغ الدفع أكبر من صفر.', 400);
            }

            $remainingPayment = $data['amount']; // Store the remaining amount for payment.

            // Distribute the payment across remaining products.
            collect($receipt->receiptProducts)->each(function ($product) use (&$remainingPayment, $totalRemainingDebt) {
                if ($remainingPayment <= 0) {
                    return;
                }

                $installment = $product->installment;

                if ($installment && $product->remaining_price > 0) {
                    // Calculate the percentage of debt for this product.
                    $percentage = $product->remaining_price / $totalRemainingDebt;

                    // Calculate the amount to be paid for this product.
                    $paymentForProduct = round($remainingPayment * $percentage, 2);

                    // Ensure the payment amount does not exceed the remaining balance.
                    $actualPayment = min($remainingPayment, $product->remaining_price, $paymentForProduct);

                    if ($actualPayment > 0) {
                        // Record a new installment payment.
                        $installment->installmentPayments()->create([
                            'payment_date' => now(),
                            'amount' => $actualPayment,

                        ]);

                        // Update the remaining payment amount.
                        $remainingPayment -= $actualPayment;
                    }
                }
            });

            DB::commit(); // Complete the transaction and save changes to the database.

            return $this->successResponse('تم دفع جزء من القسط للفاتورة بنجاح!', 200, $receipt);

        } catch (Exception $e) {
            DB::rollBack(); // Roll back all transactions in case of an error.
            Log::error('خطأ أثناء معالجة دفعة القسط: ' . $e->getMessage());

            return $this->errorResponse('حدث خطأ أثناء معالجة دفعة القسط، يرجى إعادة المحاولة.', 500);
        }
    }


}
