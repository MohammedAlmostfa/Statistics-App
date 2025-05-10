<?php

namespace App\Services;

use Exception;
use App\Models\Installment;
use App\Models\ActivitiesLog;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\InstallmentPaidEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Service class to handle installment payments including create, update, and delete operations.
 * All actions are logged and wrapped in database transactions to ensure consistency.
 */
class InstallmentPaymentService
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
                'status'       => 0,
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
                'description' => 'تم حذف دفعة قسط بمبلغ ' . $installmentPayment->amount . ' من العميل ' . $installmentPayment->installment->receipt->customer->name,
                'type_id'     => $installmentPayment->id,
                'type_type'   => InstallmentPayment::class,
            ]);

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
     * Return a standardized success response in Arabic.
     *
     * @param string $message Response message.
     * @param int $status HTTP status code (default is 200).
     * @param mixed|null $data Optional response payload.
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
     * Return a standardized error response in Arabic.
     *
     * @param string $message Response message.
     * @param int $status HTTP status code (default is 500).
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
