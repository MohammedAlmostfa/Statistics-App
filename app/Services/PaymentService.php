<?php

namespace App\Services;

use Exception;
use App\Models\Payment;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Service class for managing Payment records.
 * Handles creating, updating, and deleting payments with proper error logging.
 */
class PaymentService
{

    public function getAllPayments()
    {
        try {
            $cacheKey = 'payments';
            $payments = Cache::remember($cacheKey, now()->addMinutes(16), function () {
                return Payment::with('user:id,name')->paginate(10);
            });

            return $this->successResponse('تم استرجاع الدفعات بنجاح.', 200, $payments);
        } catch (Exception $e) {
            Log::error('Error retrieving payments: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الدفعات.');
        }
    }

    /**
     * Create a new payment entry.
     *
     * @param array $data Payment data to be saved.
     * @return array API response with status and message.
     */
    public function createPaymant(array $data): array
    {
        try {
            $userId = Auth::id(); // Get authenticated user ID

            Payment::create([
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'details' => $data['details'],
                'user_id' =>      $userId,
            ]);

            return $this->successResponse('تم إنشاء الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء الدفعة.');
        }
    }

    /**
     * Update existing payment details.
     *
     * @param array $data New data to update.
     * @param Payment $Payment Existing payment model instance.
     * @return array API response with status and message.
     */
    public function updatePayment(array $data, Payment $Payment): array
    {
        try {
            $Payment->update([
                'amount' => $data['amount'] ?? $Payment->amount,
                'payment_date' => $data['payment_date'] ?? $Payment->payment_date,
                'details' => $data['details'] ?? $Payment->details,
            ]);

            return $this->successResponse('تم تحديث بيانات الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error updating payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث بيانات الدفعة.');
        }
    }

    /**
     * Delete a payment record.
     *
     * @param Payment $Payment Payment model instance to delete.
     * @return array API response with status and message.
     */
    public function deletePayment(Payment $Payment): array
    {
        try {
            $Payment->delete(); // Remove payment from database
            return $this->successResponse('تم حذف الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error deleting payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الدفعة.');
        }
    }

    /**
     * Standard success response formatter.
     *
     * @param string $message Message to return.
     * @param int $status HTTP status code.
     * @param mixed|null $data Optional payload.
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
     * Standard error response formatter.
     *
     * @param string $message Error message.
     * @param int $status HTTP error code.
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
