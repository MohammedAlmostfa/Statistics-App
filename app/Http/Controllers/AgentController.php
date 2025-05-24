<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Services\AgentService;
use App\Http\Resources\AgentResource;
use App\Http\Requests\AgentRequest\StoreAgentData;
use App\Http\Requests\AgentRequest\UpdateAgentData;
use App\Http\Resources\FinancialTransactionResource;
use App\Http\Requests\AgentRequest\FilteringAgentData;
use Illuminate\Http\JsonResponse;

/**
 * **AgentController**
 *
 * This controller handles all agent-related CRUD operations,
 * utilizing `AgentService` to process business logic.
 */
class AgentController extends Controller
{
    /**
     * **Agent Service Instance**
     *
     * @var AgentService Handles agent-related operations.
     */
    protected AgentService $agentService;

    /**
     * **Constructor**
     *
     * Initializes the controller with `AgentService` to delegate logic processing.
     *
     * @param AgentService $agentService Handles agent-related operations.
     */
    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * **Retrieve a paginated list of agents**
     *
     * - Uses `FilteringAgentData` for optional filtering criteria.
     * - Data is retrieved via `AgentService`.
     * - Results are formatted using `AgentResource`.
     *
     * @param FilteringAgentData $request Filtering data parameters.
     * @return \Illuminate\Http\JsonResponse Paginated list of agents.
     */
    public function index(FilteringAgentData $request): JsonResponse
    {
        $validatedData = $request->validated();
        $result = $this->agentService->getAllAgents($validatedData);

        return $result['status'] === 200
            ? $this->paginated($result['data'], AgentResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Create a new agent**
     *
     * - Validates data using `StoreAgentData`.
     * - Creates an agent via `AgentService`.
     *
     * @param StoreAgentData $request The request containing agent details.
     * @return \Illuminate\Http\JsonResponse Creation result.
     */
    public function store(StoreAgentData $request): JsonResponse
    {
        $validatedData = $request->validated();
        $result = $this->agentService->createAgent($validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Update an existing agent**
     *
     * - Validates data using `UpdateAgentData`.
     * - Updates the agent via `AgentService`.
     *
     * @param UpdateAgentData $request Contains updated agent details.
     * @param Agent $agent The agent instance to be updated.
     * @return \Illuminate\Http\JsonResponse Update result.
     */
    public function update(UpdateAgentData $request, Agent $agent): JsonResponse
    {
        $validatedData = $request->validated();
        $result = $this->agentService->updateAgent($validatedData, $agent);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Delete an agent**
     *
     * - Removes the specified agent via `AgentService`.
     *
     * @param Agent $agent The agent instance to be deleted.
     * @return \Illuminate\Http\JsonResponse Deletion result.
     */
    public function destroy(Agent $agent): JsonResponse
    {
        $this->authorize('deleteAgent', $agent);

        $result = $this->agentService->deleteAgent($agent);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Retrieve financial transactions for an agent**
     *
     * - Fetches financial transactions associated with an agent.
     * - Provides paginated results using `FinancialTransactionResource`.
     *
     * @param int $id The agent ID.
     * @return \Illuminate\Http\JsonResponse Paginated list of financial transactions.
     */
    public function getaAentFinancialTransactions($id): JsonResponse
    {
        $result = $this->agentService->GetFinancialTransactions($id);

        return $result['status'] === 200
            ? $this->paginated($result['data'], FinancialTransactionResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
