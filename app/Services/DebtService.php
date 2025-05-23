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
 * Class DebtService
 *
 * Provides various functionalities for managing debts, including retrieval,
 * creation, deletion, and caching mechanisms to optimize performance.
 */
class DebtService extends Service
{
    /**
     * Retrieve all debts with optional filtering and pagination.
     *
     * This method utilizes caching to store frequently accessed debts, reducing
     * repeated database queries and improving performance.
     *
     * @param array $filteringData Optional filtering parameters.
     * @return array JSON response containing retrieved debts.
     */
    public function getAllDebts($filteringData)
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'debts_' . $page . '_' . md5(json_encode($filteringData));

            // Store cache keys to facilitate clearing when needed
            $cacheKeys = Cache::get('all_debts_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_debts_keys', $cacheKeys, now()->addHours(2));
            }

            // Retrieve debts with filtering and caching
            $debts = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($filteringData) {
                return Debt::with(['user:id,name', 'customer:id,name'])
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->orderByDesc('debt_date')
                    ->paginate(10);
            });

            return $this->successResponse('تم استرجاع الديون بنجاح.', 200, $debts);
        } catch (Exception $e) {
            Log::error('Error retrieving debts: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الديون.');
        }
    }


    /**
     * Create a new debt record.
     *
     * This method initializes a new debt entry, linking it to the associated
     * customer and user who created it. It also logs the transaction in the system.
     *
     * @param array $data Debt data required for creation.
     * @return array JSON response indicating success or failure.
     */
    public function createDebt(array $data): array
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Create a new debt record
            $Debt = Debt::create([
                'customer_id'    => $data['customer_id'],
                'remaining_debt' => $data['remaining_debt'],
                'payment_amount' => $data['payment_amount'],
                'debt_date'      => $data['debt_date'],
                'description'    => $data['description'] ?? null,
                'user_id'        => $userId,
            ]);

            // Log the transaction in the activity logs
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل دفعة دين للزبون ' . $Debt->customer->name,
                'type_id'     => $Debt->id,
                'type_type'   => Debt::class,
            ]);

            DB::commit();

            return $this->successResponse('تم إنشاء الدين بنجاح.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating debt: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء انشاء دين , يرجى المحاولة مرة اخرى ');

        }
    }

    /**
     * Delete an existing debt record.
     *
     * This method removes a debt entry from the database and logs the deletion
     * event for recordkeeping.
     *
     * @param Debt $Debt Debt model instance to be deleted.
     * @return array JSON response indicating success or failure.
     */
    public function deleteDebt(Debt $Debt): array
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Log deletion in the activity records
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف دفعة دين للزبون ' . $Debt->customer->name,
                'type_id'     => $Debt->id,
                'type_type'   => Debt::class,
            ]);

            // Remove the debt record
            $Debt->delete();

            DB::commit();

            return $this->successResponse('تم حذف الدين بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting debt: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء حذف دين , يرجى المحاولة مرة اخرى ');
        }
    }
}
