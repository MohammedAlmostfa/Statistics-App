<?php

namespace App\Services;

use Exception;
use App\Models\DebtPayment;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * DebtPaymentService: Handles debt payment-related business logic.
 */
class DebtPaymentService extends Service
{
    /**
     * Create a new debt payment.
     *
     * @param array $data Payment data.
     * @return array Success or error response.
     */
    public function createDebtPayment(array $data)
    {
        DB::beginTransaction();

        try {
            // Get authenticated user
            $userId = Auth::id();

            // Create debt payment record
            $debtPayment = DebtPayment::create([
                'amount'          => $data['amount'],
                'debt_id'         => $data['debt_id'],
                'user_id'         => $userId,
                'payment_date'    => $data['payment_date'],
            ]);

            // Log the activity
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تحصيل مبلغ قدره ' . $data['amount'] . ' من العميل ' .$debtPayment->debt->customer->name,
                'type_id'     => $debtPayment->id,
                'type_type'   => DebtPayment::class,
            ]);

            DB::commit();
            return $this->successResponse("تم تسجيل دفعة الدين بنجاح.", 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error creating debt payment: " . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء انشاء دفعة الدين , يرجى المحاولة مرة اخرى ');

        }
    }

    /**
     * Delete a debt payment.
     *
     * @param DebtPayment $debtPayment Payment record to delete.
     * @return array Success or error response.
     */
    public function deleteDebtPayment(DebtPayment $debtPayment)
    {
        DB::beginTransaction();

        try {
            // Get authenticated user
            $userId = Auth::id();

            // Log the activity
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف دفعة قسط بمبلغ ' . $debtPayment->amount . ' من العميل ' . $debtPayment->debt->customer->name,
                'type_id'     => $debtPayment->id,
                'type_type'   => DebtPayment::class,
            ]);

            $debtPayment->delete();
            DB::commit();

            return $this->successResponse("تم حذف دفعة الدين بنجاح.", 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting debt payment: " . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء حذف دفعة الدين , يرجى المحاولة مرة اخرى ');
        }
    }
}
