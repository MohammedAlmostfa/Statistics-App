<?php

namespace App\Services;

use Exception;
use App\Models\Agent;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * **AgentService**
 *
 * This service provides methods for managing agent records,
 * including retrieving, creating, updating, and deleting agents.
 * It also supports caching and error logging for optimized performance.
 */
class AgentService extends Service
{
    /**
     * **Retrieve all agents with optional filtering**
     *
     * - Supports caching to optimize database queries.
     * - Retrieves agents with pagination.
     *
     * @param array $filteringData Optional filtering parameters.
     * @return array The response containing the list of agents.
     */
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
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->where("status", 'موجود')->orderByDesc('created_at')
                    ->orderByDesc('created_at')
                    ->with(["lastfinancialTransaction",'lastfinancialTransactionPaid'])
                    ->paginate(10);
            });

            return $this->successResponse('تم استرجاع بيانات الوكلاء بنجاح.', 200, $agents);
        } catch (Exception $e) {
            Log::error('General error while retrieving agents: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء استرجاع بيانات الوكلاء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * **Create a new agent**
     *
     * - Inserts a new agent record into the database.
     * - Logs the creation in `ActivitiesLog`.
     *
     * @param array $data The agent data.
     * @return array Response indicating success or failure.
     */
    public function createAgent(array $data): array
    {
        try {
            $agent = Agent::create($data);
            $userId = Auth::id();

            // Log the creation of the new agent
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

    /**
     * **Update an existing agent**
     *
     * - Modifies agent details in the database.
     * - Logs the update in `ActivitiesLog`.
     *
     * @param array $data The updated agent data.
     * @param Agent $agent The agent instance to update.
     * @return array Response indicating success or failure.
     */
    public function updateAgent(array $data, Agent $agent): array
    {
        try {
            $agent->update($data);
            $userId = Auth::id();

            // Log the update
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

    /**
     * **Delete an agent**
     *
     * - Removes the agent record from the database.
     * - Logs the deletion in `ActivitiesLog`.
     *
     * @param Agent $agent The agent instance to delete.
     * @return array Response indicating success or failure.
     */
    public function deleteAgent(Agent $agent): array
    {
        try {
            $userId = Auth::id();

            // Log the deletion
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم حذف وكيل: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            $agent->update(['status' => "محذوف"]);

            return $this->successResponse('تم حذف الوكيل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while deleting agent: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف الوكيل، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * **Retrieve financial transactions for an agent**
     *
     * - Fetches financial transactions associated with a specific agent.
     * - Provides paginated results.
     *
     * @param int $id The agent ID.
     * @return array Response containing financial transaction data.
     */
    public function GetFinancialTransactions($id, $data)
    {
        try {
            // Retrieve paginated financial transactions related to the agent
            $FinancialTransactions = FinancialTransaction::where('agent_id', $id)
                ->with('user:id,name')
                ->when(isset($data['transaction_date']), function ($query) use ($data) {
                    return $query->where('transaction_date', '>=', $data['transaction_date']);
                })
                ->get();

            return $this->successResponse('تم استرجاع المعاملات المالية للوكيل بنجاح', 200, $FinancialTransactions);
        } catch (Exception $e) {
            Log::error('Error while retrieving financial transactions: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء جلب المعاملات المالية، يرجى المحاولة مرة أخرى.');
        }
    }
}
