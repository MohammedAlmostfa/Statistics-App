<?php

namespace App\Services;

use Exception;
use App\Models\Payment;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service class to handle payments.
 * Includes methods for creating, updating, deleting, and retrieving payments with caching.
 */
class PaymentService extends Service
{
    /**
     * Retrieve all payments with pagination, utilizing cache to improve performance.
     *
     * @return array Structured success or error response in Arabic.
     */
    public function getAllPayments()
{
    try {
        // Paginated payments
        $payments = Payment::with('user:id,name')
            ->orderByDesc('payment_date')
            ->paginate(10);

        // Total sum
        $totalPaid = Payment::sum('amount');

        return $this->successResponse('تم استرجاع الدفعات بنجاح.', 200, [
            'payments' => $payments,
            'totalPaid' => $totalPaid
        ]);
    } catch (Exception $e) {
        Log::error('Error retrieving payments: ' . $e->getMessage());
        return $this->errorResponse('حدث خطأ اثناء استرجاع الدفعات , يرجى المحاولة مرة اخرى');
    }
}


    /**
     * Create a new payment and log the activity.
     *
     * @param array $data The payment data, including 'amount', 'payment_date', and 'details'.
     * @return array Structured success or error response in Arabic.
     */
    public function createPayment(array $data): array
    {
        DB::beginTransaction();

        try {
            // Get the authenticated user ID
            $userId = Auth::id();

            // Create a new payment record in the database
            $payment = Payment::create([
                'amount'       => $data['amount'],
                'payment_date' => $data['payment_date'],
                'details'      => $data['details'],
                'user_id'      => $userId,
            ]);

            // Log the activity of adding a new payment
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة دفعة بمبلغ ' . $payment->amount,
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('تم إنشاء الدفعة بنجاح.', 201);
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء انشاء الدفعة , يرجى المحاولة مرة اخرى ');
        }
    }

    /**
     * Update an existing payment and log the activity.
     *
     * @param array $data The updated payment data, including 'amount', 'payment_date', and 'details'.
     * @param Payment $payment The payment object to update.
     * @return array Structured success or error response in Arabic.
     */
    public function updatePayment(array $data, Payment $payment): array
    {
        DB::beginTransaction();

        try {
            // Get the authenticated user ID
            $userId = Auth::id();

            // Log the activity of updating the payment amount
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل دفعة من ' . $payment->amount . ' إلى ' . $data['amount'],
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            // Update the payment with the new data
            $payment->update([
                'amount'       => $data['amount'] ?? $payment->amount,
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'details'      => $data['details'] ?? $payment->details,
            ]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('تم تحديث بيانات الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            Log::error('Error updating payment: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء تحديث الدفعة , يرجى المحاولة مرة اخرى ');
        }
    }

    /**
     * Delete a payment and log the activity.
     *
     * @param Payment $payment The payment object to delete.
     * @return array Structured success or error response in Arabic.
     */
    public function deletePayment(Payment $payment): array
    {
        DB::beginTransaction();

        try {
            // Get the authenticated user ID
            $userId = Auth::id();

            // Log the activity of deleting the payment
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف دفعة بمبلغ ' . $payment->amount,
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            // Delete the payment from the database
            $payment->delete();

            // Commit the transaction
            DB::commit();

            return $this->successResponse('تم حذف الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            Log::error('Error deleting payment: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء حذف الدفعة , يرجى المحاولة مرة اخرى ');
        }
    }
}
