<?php

namespace App\Services;

use Exception;
use App\Events\ProductEvent;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialTransactions;
use Illuminate\Database\QueryException;

/**
 * **TransactionService**
 *
 * This service handles financial transactions, including:
 * - Creating new financial transactions
 * - Associating products with financial transactions
 * - Logging activities in `ActivitiesLog`
 * - Managing errors with database rollback for consistency
 */
class FinancialTransactionService extends Service
{
    /**
     * **Create a new financial transaction**
     *
     * - Starts a **database transaction** to ensure data integrity.
     * - Creates a new **FinancialTransactions record**.
     * - Adds associated products.
     * - Logs activity in `ActivitiesLog`.
     * - Handles errors and rolls back the transaction if needed.
     *
     * @param array $data Transaction details from the request.
     * @return \Illuminate\Http\JsonResponse Success or error response.
     */
    public function StoreFinancialTransaction($data)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();


            $financialTransactions = FinancialTransactions::create([
                'agent_id' => $data["agent_id"],
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'فاتورة شراء',
                'total_amount' => $data["total_amount"],
                'discount_amount' => $data["discount_amount"],
                'paid_amount' => $data["paid_amount"],
                'description' => $data["description"],
            ]);


            $products = $data['products'];
            foreach ($products as $product) {
                $financialTransactions->financialTransactionsProducts()->create([
                    'product_id' => $product['product_id'],
                    'selling_price' => $product['selling_price'],
                    'dollar_buying_price' => $product['dollar_buying_price'],
                    'dollar_exchange' => $product['dollar_exchange'],
                    'quantity' => $product['quantity'],
                ]);


                event(new ProductEvent($product));
            }


            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تمت إضافة معاملة مالية جديدة للوكيل: ' . $financialTransactions->agent->name,
                'type_id' => $financialTransactions->id,
                'type_type' => FinancialTransactions::class,
            ]);

            DB::commit();
            return $this->successResponse('تم إنشاء المعاملة المالية بنجاح.', 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ عام أثناء معالجة المعاملة المالية: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حفظ المعاملة المالية.');
        }
    }

    /**
     * **Update a financial transaction**
     *
     * - Modifies financial transaction details in `FinancialTransactions`.
     * - Adds or updates associated products.
     * - Logs the changes in `ActivitiesLog`.
     *
     * @param array $data Updated transaction details.
     * @param FinancialTransactions $financialTransactions The financial transaction to update.
     * @return \Illuminate\Http\JsonResponse Success or error response.
     */
    public function updateFinancialTransactionsItem($data, FinancialTransactions $financialTransactions)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();


            $financialTransactions->update([
                'agent_id' => $data["agent_id"] ?? $financialTransactions->agent_id,
                'transaction_date' => $data["transaction_date"] ?? now() ?? $financialTransactions->transaction_date,
                'total_amount' => $data["total_amount"] ?? $financialTransactions->total_amount,
                'discount_amount' => $data["discount_amount"] ?? $financialTransactions->discount_amount,
                'paid_amount' => $data["paid_amount"] ?? $financialTransactions->paid_amount,
                'description' => $data["description"] ?? $financialTransactions->description,
            ]);


            $existingProducts = $financialTransactions->financialTransactionsItem->keyBy('product_id');


            $newProducts = collect($data['products'])->keyBy('product_id');


            $productsToDelete = $existingProducts->diffKeys($newProducts);
            foreach ($productsToDelete as $product) {
                $product->delete();
            }


            $productsToUpdate = $existingProducts->intersectByKeys($newProducts);
            foreach ($productsToUpdate as $product) {
                $updatedProduct = $newProducts[$product->product_id];
                $product->update([
                    'selling_price' => $updatedProduct['selling_price'] ?? $product->selling_price,
                    'dollar_buying_price' => $updatedProduct['dollar_buying_price'] ?? $product->dollar_buying_price,
                    'dollar_exchange' => $updatedProduct['dollar_exchange'] ?? $product->dollar_exchange,
                    'quantity' => $updatedProduct['quantity'] ?? $product->quantity,
                ]);
            }


            $productsToAdd = $newProducts->diffKeys($existingProducts);
            foreach ($productsToAdd as $product) {
                $financialTransactions->financialTransactionsProducts()->create([
                    'product_id' => $product['product_id'],
                    'selling_price' => $product['selling_price'],
                    'dollar_buying_price' => $product['dollar_buying_price'],
                    'dollar_exchange' => $product['dollar_exchange'],
                    'quantity' => $product['quantity'],
                ]);
            }


            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تحديث المعاملة المالية للوكيل: ' . $financialTransactions->agent->name,
                'type_id' => $financialTransactions->id,
                'type_type' => FinancialTransactions::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تحديث المعاملة المالية والمنتجات بنجاح.', 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث المعاملة المالية: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث المعاملة المالية.');
        }
    }
}
