<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransactionService;

use App\Models\FinancialTransactions;
use App\Services\FinancialTransactionService;
use App\Http\Requests\FinancialTransactionRequest\UpdateTransactionData;
use App\Http\Requests\FinancialTransactionRequest\StoreFinancialTransactionData;
use App\Http\Requests\FinancialTransactionRequest\UpdateFinancialTransactionData;

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
     * @return \Illuminate\Http\JsonResponse List of transactions.
     */
    public function index()
    {

    }

    /**
     * **Store a new transaction**
     *
     * - Validates transaction data via `StoreTransactionData`.
     * - Saves the transaction using `TransactionService`.
     *
     * @param StoreTransactionData $request Data for transaction creation.
     * @return \Illuminate\Http\JsonResponse Result of the operation.
     */
    public function store(StoreFinancialTransactionData $request)
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
     * @return \Illuminate\Http\JsonResponse Update result.
     */
    public function update(UpdateFinancialTransactionData $request, $id)
    {
        $validatedData = $request->validated();
        $financialTransaction=FinancialTransactions::findOrFail($id);
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
     * @return \Illuminate\Http\JsonResponse Deletion result.
     */
    public function destroy($id)
    {
        $financialTransaction=FinancialTransactions::findOrFail($id);

        $result = $this->financialTransactionService->deleteFinancialTransaction($financialTransaction);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
