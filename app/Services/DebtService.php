<?php

namespace App\Services;

use Exception;
use App\Models\Payment;
use App\Models\ActivitiesLog;
use App\Models\Debt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service class to handle payments and debts.
 */
class DebtService
{
    /**
 * Retrieve all debts with pagination.
 */
    public function getAllDebts($filteringData)
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'debts_' . $page . '_' . md5(json_encode($filteringData));

            $cacheKeys = Cache::get('all_debts_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_debts_keys', $cacheKeys, now()->addHours(2));
            }

            $receipts = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($filteringData) {
                return Debt::with(['user:id,name', 'customer:id,name'])
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->orderByDesc('debt_date')
                    ->paginate(10);
            });

            return $this->successResponse('تم استرجاع الديون بنجاح.', 200, $receipts);
        } catch (Exception $e) {
            Log::error('Error retrieving debts: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الديون.');
        }
    }

    /**
     * Create a new debt record.
     */
    public function createDebt(array $data): array
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            $Debt = Debt::create([
                'receipt_number' => $data['receipt_number'],
                'customer_id' => $data['customer_id'],
                'remaining_debt' => $data['remaining_debt'],
                'total_debt' => $data['total_debt'],
                'debt_date' => $data['debt_date'],
                'user_id' => $userId,
            ]);

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تعديل دفعة دين للزبون ' . $Debt->customer->name,
                'type_id' => $Debt->id,
                'type_type' => Debt::class,
            ]);

            DB::commit();

            return $this->successResponse('تم إنشاء الدين بنجاح.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating debt: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء الدين.');
        }
    }

    /**
     * Delete a debt record.
     */
    public function deleteDebt(Debt $Debt): array
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم حذف دفعة دين للزبون ' . $Debt->customer->name,
                'type_id' => $Debt->id,
                'type_type' => Debt::class,
            ]);

            $Debt->delete();

            DB::commit();

            return $this->successResponse('تم حذف الدين بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting debt: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الدين.');
        }
    }

    /**
     * Standardized success response.
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
     * Standardized error response.
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
