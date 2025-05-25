<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DebtResource;
use App\Http\Requests\DebetRequest\StoreDebtData;

use App\Http\Requests\DebetRequest\FitteringDebetgData;

class DebtController extends Controller
{
    /**
     * Handles debt-related business logic.
     *
     * @var DebtService
     */
    protected DebtService $DebtService;

    /**
     * DebtController Constructor
     *
     * @param DebtService $DebtService
     */
    public function __construct(DebtService $DebtService)
    {
        $this->DebtService = $DebtService;
    }

    /**
     * Display a listing of debts.
     *
     * @return JsonResponse
     */
    public function index(FitteringDebetgData $request): JsonResponse
    {
        $result = $this->DebtService->getAllDebts($request->validated());

        return $result['status'] === 200
            ? $this->paginated($result['data'], DebtResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Store a newly created debt record.
     *
     * @param StoreDebtData $request
     * @return JsonResponse
     */
    public function store(StoreDebtData $request): JsonResponse
    {
        $result = $this->DebtService->createDebt($request->validated());

        return $result['status'] === 201
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }


    /**
     * Remove a debt record.
     *
     * @param Debt $Debt
     * @return JsonResponse
     */
    public function destroy(Debt $Debt): JsonResponse
    {
        $result = $this->DebtService->deleteDebt($Debt);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
