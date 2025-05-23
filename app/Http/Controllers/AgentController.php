<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Services\AgentService;
use App\Http\Resources\AgentResource;
use App\Http\Requests\AgentRequest\StoreAgentData;
use App\Http\Requests\AgentRequest\UpdateAgentData;
use App\Http\Resources\FinancialTransactionResource;
use App\Http\Requests\AgentRequest\FilteringAgentData;

/**
 * **AgentController**
 *
 * This controller manages CRUD operations for agents,
 * utilizing `AgentService` to handle business logic.
 */
class AgentController extends Controller
{
    /**
     * **Agent Service Instance**
     *
     * This service handles agent-related operations.
     *
     * @var AgentService
     */
    protected AgentService $agentService;

    /**
     * **Constructor**
     *
     * Injects `AgentService` into the controller for managing agent operations.
     *
     * @param AgentService $agentService Handles business logic for agents.
     */
    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * **Retrieve a list of agents**
     *
     * Uses `FilteringAgentData` to filter retrieved data.
     * - Data is retrieved via `AgentService`.
     * - Results are presented through `AgentResource` in descending order.
     *
     * @param FilteringAgentData $request Filtering data parameters.
     * @return \Illuminate\Http\JsonResponse List of agents.
     */
    public function index(FilteringAgentData $request)
    {
        $validatedData = $request->validated();
        $result = $this->agentService->getAllAgents($validatedData);

        return $result['status'] === 200
            ? $this->paginated($result['data'], AgentResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Store a new agent**
     *
     * - Validates data using `StoreAgentData`.
     * - Creates a new agent via `AgentService`.
     *
     * @param StoreAgentData $request Data for creating an agent.
     * @return \Illuminate\Http\JsonResponse Result of the operation.
     */
    public function store(StoreAgentData $request)
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
     * @param UpdateAgentData $request Updated agent details.
     * @param Agent $agent The agent instance to be updated.
     * @return \Illuminate\Http\JsonResponse Update result.
     */
    public function update(UpdateAgentData $request, Agent $agent)
    {
        $validatedData = $request->validated();
        $result = $this->agentService->updateAgent($validatedData, $agent);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Delete an agent from the database**
     *
     * - Deletes the specified agent via `AgentService`.
     *
     * @param Agent $agent The agent instance to be deleted.
     * @return \Illuminate\Http\JsonResponse Deletion result.
     */
    public function destroy(Agent $agent)
    {
        $result = $this->agentService->deleteAgent($agent);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }


    public function getaAentFinancialTransactions($id)
    {
        $result = $this->agentService->GetFinancialTransactions($id);

        return $result['status'] === 200
            ? $this->paginated($result['data'], FinancialTransactionResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
