<?php

namespace App\Services;

use Exception;
use App\Models\Payment;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function getAllPayments()
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'payments_page_' . $page;

            $payments = Cache::remember($cacheKey, now()->addMinutes(16), function () {
                return Payment::with('user:id,name')->paginate(10);
            });

            return $this->successResponse('تم استرجاع الدفعات بنجاح.', 200, $payments);
        } catch (Exception $e) {
            Log::error('Error retrieving payments: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الدفعات.');
        }
    }

    public function createPayment(array $data): array
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $payment = Payment::create([
                'amount'       => $data['amount'],
                'payment_date' => $data['payment_date'],
                'details'      => $data['details'],
                'user_id'      => $userId,
            ]);

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة دفعة بمبلغ ' . $payment->amount,
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            DB::commit();
            return $this->successResponse('تم إنشاء الدفعة بنجاح.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء الدفعة.');
        }
    }

    public function updatePayment(array $data, Payment $payment): array
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل دفعة من ' . $payment->amount . ' إلى ' . $data['amount'],
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            $payment->update([
                'amount'       => $data['amount'] ?? $payment->amount,
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'details'      => $data['details'] ?? $payment->details,
            ]);

            DB::commit();
            return $this->successResponse('تم تحديث بيانات الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث بيانات الدفعة.');
        }
    }

    public function deletePayment(Payment $payment): array
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف دفعة بمبلغ ' . $payment->amount,
                'type_id'     => $payment->id,
                'type_type'   => Payment::class,
            ]);

            $payment->delete();

            DB::commit();
            return $this->successResponse('تم حذف الدفعة بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting payment: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الدفعة.');
        }
    }

    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status'  => $status,
            'data'    => $data,
        ];
    }

    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status'  => $status,
        ];
    }
}
