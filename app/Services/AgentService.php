<?php

namespace App\Services;

use Exception;
use App\Models\Debt;
use App\Models\Agent;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use App\Http\Resources\CustomerReceiptProduct;

/**use Illuminate\Support\Facades\Auth;

 * CustomerService
 *
 * This service provides methods for managing customer records,
 * including retrieving, creating, updating, and deleting customers.
 * It also supports caching and error logging for optimized performance.
 */

class AgentService extends Service
{
    public function getAllAgents($filteringData): array
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'agents_' . $page . (empty($filteringData) ? '' : md5(json_encode($filteringData)));
            $cacheKeys = Cache::get('all_agents_keys', []);

            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_agents_keys', $cacheKeys, now()->addHours(2));
            }

            // Retrieve agents from cache or fetch from the database
            $agents = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($filteringData) {
                return Agent::query()
                    ->when(!empty($filteringData), fn ($query) => $query->where($filteringData))
                    ->orderByDesc('created_at')
                    ->paginate(10);
            });

            return $this->successResponse('تم جلب بيانات الوكلاء بنجاح.', 200, $agents);
        } catch (QueryException $e) {
            Log::error('Database query error while retrieving agents: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب بيانات الوكلاء.');
        } catch (Exception $e) {
            Log::error('General error while retrieving agents: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء استرجاع بيانات الوكلاء، يرجى المحاولة مرة أخرى.');
        }
    }


    public function createAgent(array $data): array
    {
        try {
            $agent = Agent::create($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم إضافة وكيل: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            return $this->successResponse('تم إنشاء وكيل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while creating agent: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء إنشاء وكيل، يرجى المحاولة مرة أخرى.');
        }
    }

    public function updateAgent(array $data, Agent $agent): array
    {
        try {
            $agent->update($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تعديل وكيل: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            return $this->successResponse('تم تحديث بيانات الوكيل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while updating agent: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث الوكيل، يرجى المحاولة مرة أخرى.');
        }
    }

    public function deleteAgent(Agent $agent): array
    {
        try {
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم حذف وكيل: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            $agent->delete();

            return $this->successResponse('تم حذف الوكيل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while deleting agent: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف الوكيل، يرجى المحاولة مرة أخرى.');
        }
    }
}
