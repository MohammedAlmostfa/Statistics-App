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
     * **Retrieve all agents with optional filtering and caching**
     *
     * - Applies filtering criteria if provided.
     * - Implements caching for optimized performance.
     * - Returns paginated results.
     *
     * @param array|null $filteringData Optional filters (e.g., name, phone).
     * @return array Structured success or error response.
     */
    public function getAllAgents($filteringData): array
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'agents_' . $page . (empty($filteringData) ? '' : md5(json_encode($filteringData)));
            $cacheKeys = Cache::get('all_agents_keys', []);

            // Store cache key if it's new
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

            return $this->successResponse('Agents retrieved successfully.', 200, $agents);
        } catch (QueryException $e) {
            Log::error('Database query error while retrieving agents: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve agent data.');
        } catch (Exception $e) {
            Log::error('General error while retrieving agents: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while retrieving agent data. Please try again.');
        }
    }

    /**
     * **Create a new agent**
     *
     * - Saves the agent record in the database.
     * - Logs the creation action.
     *
     * @param array $data Agent details.
     * @return array Structured success or error response.
     */
    public function createAgent(array $data): array
    {
        try {
            $agent = Agent::create($data);
            $userId = Auth::id();

            // Log the creation in ActivitiesLog
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'Agent added: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            return $this->successResponse('Agent created successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error while creating agent: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while creating the agent. Please try again.');
        }
    }

    /**
     * **Update an existing agent's details**
     *
     * - Updates the agent record.
     * - Logs the update action.
     *
     * @param array $data Updated agent details.
     * @param Agent $agent Agent instance to be updated.
     * @return array Structured success or error response.
     */
    public function updateAgent(array $data, Agent $agent): array
    {
        try {
            $agent->update($data);
            $userId = Auth::id();

            // Log the update in ActivitiesLog
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'Agent updated: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            return $this->successResponse('Agent updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error while updating agent: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while updating the agent. Please try again.');
        }
    }

    /**
     * **Delete an agent record**
     *
     * - Removes the agent record from the database.
     * - Logs the deletion action.
     *
     * @param Agent $agent Agent instance to be deleted.
     * @return array Structured success or error response.
     */
    public function deleteAgent(Agent $agent): array
    {
        try {
            $userId = Auth::id();

            // Log the deletion in ActivitiesLog
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'Agent deleted: ' . $agent->name,
                'type_id' => $agent->id,
                'type_type' => Agent::class,
            ]);

            $agent->delete();

            return $this->successResponse('Agent deleted successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error while deleting agent: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while deleting the agent. Please try again.');
        }
    }
}
