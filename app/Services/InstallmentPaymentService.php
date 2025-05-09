<?php

namespace App\Services;

use Exception;
use App\Models\Installment;
use App\Models\ActivitiesLog;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallmentPaymentService
{
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

            DB::commit();

            return [
                'status'  => 201,
                'message' => 'تم تسجيل دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating installment payment: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء تسديد القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

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

            return [
                'status'  => 200,
                'message' => 'تم تحديث دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating installment payment: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء تحديث دفعة القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

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

            return [
                'status'  => 200,
                'message' => 'تم حذف دفعة القسط بنجاح',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting installment payment: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء حذف دفعة القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
