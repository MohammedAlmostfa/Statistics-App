<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialTransactionRequest\StoreDebtFinancialTransactionData;
use Illuminate\Http\JsonResponse;

use App\Models\FinancialTransaction;
use App\Services\TransactionService;
use App\Services\FinancialTransactionService;
use App\Http\Resources\FinancialTransactionsProductResource;
use App\Http\Requests\FinancialTransactionRequest\StoreFinancialTransactionData;
use App\Http\Requests\FinancialTransactionRequest\UpdateFinancialTransactionData;
use App\Http\Requests\FinancialTransactionRequest\StorePaymentFinancialTransactionData;
use App\Http\Requests\FinancialTransactionRequest\UpdateDebtFinancialTransactionData;
use App\Http\Requests\FinancialTransactionRequest\UpdatePaymentFinancialTransactionData;

/**
 * **TransactionController**
 *
 * Manages transaction-related operations such as:
 * - Storing transactions
 * - Updating transactions
 * - Deleting transactions
 * - Retrieving transaction records
 */
class FinancialTransactionController extends Controller
{
    /**
     * **Transaction Service Instance**
     *
     * Handles business logic for transactions via `TransactionService`.
     *
     * @var TransactionService
     */
    protected FinancialTransactionService $financialTransactionService;

    /**
     * **Constructor**
     *
     * Injects `TransactionService` into the controller to handle transaction logic.
     *
     * @param TransactionService $transactionService Handles transaction operations.
     */
    public function __construct(FinancialTransactionService $financialTransactionService)
    {
        $this->financialTransactionService = $financialTransactionService;
    }

    /**
     * **Retrieve all transactions**
     *
     * - Can be filtered or paginated if needed.
     * - Returns JSON response with transaction records.
     *
     * @param mixed $id Identifier for fetching transactions.
     * @return \Illuminate\Http\JsonResponse List of transactions.
     */
    public function show($id): JsonResponse
    {
        $result = $this->financialTransactionService->GetFinancialTransactionsproducts($id);
        return $result['status'] === 200
            ? $this->success(FinancialTransactionsProductResource::collection($result['data']), $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Store a new transaction**
     *
     * - Validates transaction data via `StoreTransactionData`.
     * - Saves the transaction using `TransactionService`.
     *
     * @param StoreFinancialTransactionData $request Data for transaction creation.
     * @return \Illuminate\Http\JsonResponse Result of the operation.
     */
    public function store(StoreFinancialTransactionData $request): JsonResponse
    {

        $validatedData = $request->validated();

        $result = $this->financialTransactionService->StoreFinancialTransaction($validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Update an existing transaction**
     *
     * - Accepts updated transaction data.
     * - Modifies the transaction using `TransactionService`.
     *
     * @param UpdateFinancialTransactionData $request Validated update data.
     * @param mixed $id Transaction identifier.
     * @return \Illuminate\Http\JsonResponse Update result.
     */
    public function update(UpdateFinancialTransactionData $request, $id): JsonResponse
    {

        $validatedData = $request->validated();

        $financialTransaction=FinancialTransaction::findOrFail($id);
        $result = $this->financialTransactionService->UpdateFinancialTransaction($validatedData, $financialTransaction);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * **Delete a transaction**
     *
     * - Removes a transaction from the database.
     * - Calls `TransactionService` to handle deletion.
     *
     * @param mixed $id Transaction identifier.
     * @return \Illuminate\Http\JsonResponse Deletion result.
     */
    public function destroy($id): JsonResponse
    {
        $financialTransaction=FinancialTransaction::findOrFail($id);

        $result = $this->financialTransactionService->deleteFinancialTransaction($financialTransaction);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Store a payment financial transaction linked to a specific transaction.
     *
     * @param mixed $id Transaction identifier.
     * @param StorePaymentFinancialTransactionData $request Validated payment data.
     * @return JsonResponse Operation result.
     */
    public function StorePaymentFinancialTransaction($id, StorePaymentFinancialTransactionData $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->financialTransactionService->StorePaymentFinancialTransaction($id, $validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update a payment financial transaction.
     *
     * @param mixed $id Payment transaction identifier.
     * @param UpdatePaymentFinancialTransactionData $request Validated update data.
     * @return JsonResponse Operation result.
     */
    public function UpdatePaymentFinancialTransaction($id, UpdatePaymentFinancialTransactionData $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->financialTransactionService->UpdatePaymentFinancialTransaction($id, $validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }


    /**
     * Store a debt financial transaction linked to a specific transaction.
     *
     * @param mixed $id Transaction identifier.
     * @param StoreDebtFinancialTransactionData $request Validated debt data.
     * @return JsonResponse Operation result.
     */
    public function StoreDebtFinancialTransaction($id, StoreDebtFinancialTransactionData $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->financialTransactionService->StoreDebtFinancialTransaction($id, $validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update a debt financial transaction.
     *
     * @param mixed $id Debt transaction identifier.
     * @param UpdateDebtFinancialTransactionData $request Validated update data.
     * @return JsonResponse Operation result.
     */
    public function UpdateDebtFinancialTransaction($id, UpdateDebtFinancialTransactionData $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->financialTransactionService->UpdateDebtFinancialTransaction($id, $validatedData);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
