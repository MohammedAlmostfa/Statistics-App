<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use Exception;
use Illuminate\Support\Facades\Log;

class InstallmentPaymentService
{
    /**
     * Create a new installment payment.
     *
     * @param array $data Payment data (already validated).
     * @param string $id Installment ID.
     * @return array Status, message, and optional payment.
     */
    public function createInstallmentPayment(array $data, $id): array
    {
        try {
            $installment = Installment::with('receiptProduct.product')->findOrFail($id);

            $installment->installmentPayments()->create([
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'status' => 0,
            ]);

            return [
                'status' => 201,
                'message' => 'تم تسجيل دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Error creating installment payment: ' . $e->getMessage(), [
                'installment_id' => $installment->id ?? null,
                'amount' => $data['amount'] ?? null,

            ]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تسديد القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing installment payment.
     *
     * @param array $data Payment data (already validated).
     * @param string $id InstallmentPayment ID.
     * @return array Status, message, and optional updated payment.
     */
    public function updateInstallmentPayment(array $data, $id): array
    {
        try {
            $installmentPayment = InstallmentPayment::findOrFail($id);

            $installmentPayment->update([
                'amount' => $data['amount'],
            ]);

            return [
                'status' => 200,
                'message' => 'تم تحديث دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Error updating installment payment: ' . $e->getMessage(), [
                'installment_payment_id' => $id,
                'amount' => $data['amount'] ?? null,
                'status' => $data['status'] ?? null,

            ]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث دفعة القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete an installment payment.
     *
     * @param string $id InstallmentPayment ID.
     * @return array Status and message.
     */
    public function deleteInstallmentPayment(InstallmentPayment $installmentPayment): array
    {
        try {

            $installmentPayment->delete();

            return [
                'status' => 200,
                'message' => 'تم حذف دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Error deleting installment payment: ' . $e->getMessage(), [

            ]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف دفعة القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
