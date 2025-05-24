<?php

namespace App\Services;

use Exception;
use App\Events\ProductEvent;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialTransactions;
use App\Models\FinancialTransactionsProduct;

/**
 * **FinancialTransactionService**
 *
 * This service handles financial transactions, including:
 * - Creating new financial transactions.
 * - Associating products with financial transactions.
 * - Logging activities in `ActivitiesLog`.
 * - Managing errors using database rollback for consistency.
 */
class FinancialTransactionService extends Service
{




    /**
     * Retrieve products associated with a financial transaction.
     *
     * This method fetches all products linked to a specific financial transaction.
     * If an error occurs, it logs the issue and returns an appropriate response.
     *
     * @param int $id The financial transaction ID.
     * @return \Illuminate\Http\JsonResponse Response containing products or an error message.
     */
    public function GetFinancialTransactionsproducts($id)
    {
        try {
            // Retrieve all products associated with the given financial transaction
            $products = FinancialTransactionsProduct::where('financial_id', $id)
                ->with('product:id,name')
                ->get();

            return $this->successResponse('تم استرجاع منتجات فاتورة الشراء بنجاح.', 200, $products);
        } catch (Exception $e) {
            // Log the error details for debugging
            Log::error('خطأ أثناء استرجاع المنتجات المرتبطة بفاتورة الشراء: ' . $e->getMessage());

            return $this->errorResponse('حدث خطأ أثناء جلب المنتجات المرتبطة بفاتورة الشراء، يرجى المحاولة مرة أخرى.');
        }
    }



    /**
     * **Create a new financial transaction**
     *
     * - Starts a **database transaction** to ensure data integrity.
     * - Creates a new `FinancialTransactions` record.
     * - Adds associated products to the transaction.
     * - Logs the activity in `ActivitiesLog`.
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
            // Retrieve the last financial transaction for the agent
            $lastfinancialTransaction = FinancialTransaction::where('agent_id', $data["agent_id"])->latest()->first();

            // Calculate sum_amount safely, ensuring previous transactions exist
            $sumamount = optional($lastfinancialTransaction)->sum_amount ?? 0;
            $sumamount += ($data["total_amount"] ?? 0) - ($data["discount_amount"] ?? 0) - ($data["paid_amount"] ?? 0);

            // Creating a new financial transaction record
            $financialTransaction = FinancialTransaction::create([
                'agent_id' => $data["agent_id"],
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'فاتورة شراء',
                'total_amount' => $data["total_amount"],
                'discount_amount' => $data["discount_amount"],
                'paid_amount' => $data["paid_amount"],
                'description' => $data["description"] ?? null,
                'sum_amount' => $sumamount,
                'user_id' => $userId,
            ]);

            // Adding products to the transaction
            $products = $data['products'];
            foreach ($products as $product) {
                $financialTransaction->financialTransactionsProducts()->create([
                    'product_id' => $product['product_id'],
                    'selling_price' => $product['selling_price'],
                    'installment_price' => $product['installment_price'],
                    'dollar_buying_price' => $product['dollar_buying_price'],
                    'dollar_exchange' => $product['dollar_exchange'],
                    'quantity' => $product['quantity'],
                ]);

                // Trigger product event for tracking changes
                event(new ProductEvent($product));
            }

            // Log activity for financial transaction creation
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تمت إضافة  فاتورة شراء جديدة للوكيل: ' . $financialTransaction->agent->name,
                'type_id' => $financialTransaction->id,
                'type_type' => FinancialTransaction::class,
            ]);

            DB::commit();
            return $this->successResponse('تم إنشاء  فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ عام أثناء معالجة   فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حفظ  فاتورة الشراء يرجى المحاولة مرة أخرى.');
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
    public function UpdateFinancialTransaction($data, FinancialTransaction $financialTransaction)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Retrieve the last financial transaction (previous one)
            $lastFinancialTransactions = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                ->where('id', '<', $financialTransaction->id)
                ->latest('id')
                ->first();


            // Calculate the updated sum amount
            $sumAmount = optional($lastFinancialTransactions)->sum_amount ?? 0;
            $sumAmount += ($data["total_amount"] ?? $financialTransaction->total_amount)
                - ($data["discount_amount"] ?? $financialTransaction->discount_amount)
                - ($data["paid_amount"] ?? $financialTransaction->paid_amount);

            // Update financial transaction details
            $financialTransaction->update([
                'agent_id'        => $data["agent_id"] ?? $financialTransaction->agent_id,
                'type'            => $financialTransaction->type,
                'transaction_date' => $data["transaction_date"] ?? $financialTransaction->transaction_date,
                'total_amount'    => $data["total_amount"] ?? $financialTransaction->total_amount,
                'discount_amount' => $data["discount_amount"] ?? $financialTransaction->discount_amount,
                'paid_amount'     => $data["paid_amount"] ?? $financialTransaction->paid_amount,
                'description'     => $data["description"] ?? $financialTransaction->description,
                'sum_amount'      => $sumAmount,
            ]);
            $LastSumAmount = $sumAmount;
            $affectedTransactions = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                ->where('id', '>', $financialTransaction->id)
                ->orderBy('id')
                ->get();


            foreach ($affectedTransactions as $transaction) {
                if ($transaction->type == 'تسديد فاتورة شراء') {
                    $sumAmount = $LastSumAmount - $transaction->paid_amount;
                } else {
                    $sumAmount = $LastSumAmount
                        - ($transaction->total_amount)
                        - ($transaction->discount_amount)
                        - ($transaction->paid_amount);
                }

                $transaction->update([
                    'sum_amount'      => $sumAmount,
                ]);
                $LastSumAmount = $sumAmount;
            }

            // Process product updates
            if (!empty($data['products'])) {
                // Retrieve existing products linked to the transaction
                $existingProducts = $financialTransaction->financialTransactionsProducts->keyBy('product_id');
                $newProducts = collect($data['products'])->keyBy('product_id');

                // Identify products to be deleted
                $productsToDelete = $existingProducts->diffKeys($newProducts);


                foreach ($productsToDelete as $product) {

                    event(new ProductEvent(['product_id' => $product['product_id']]));

                    $product->delete();
                }

                // Identify products to be updated
                $productsToUpdate = $existingProducts->intersectByKeys($newProducts);
                foreach ($productsToUpdate as $product) {
                    $updatedProduct = $newProducts[$product->product_id];
                    $quantity = $updatedProduct['quantity'] - $product->quantity;

                    $product->update([
                        'selling_price' => $updatedProduct['selling_price'] ?? $product->selling_price,
                        'installment_price' => $updatedProduct['installment_price'] ?? $product->installment_price,
                        'dollar_buying_price' => $updatedProduct['dollar_buying_price'] ?? $product->dollar_buying_price,
                        'dollar_exchange' => $updatedProduct['dollar_exchange'] ?? $product->dollar_exchange,
                        'quantity' => $updatedProduct['quantity'] ?? $product->quantity,
                    ]);
                    $product->quantity = $quantity;

                    event(new ProductEvent($product->toArray()));
                }

                // Identify new products to be added
                $productsToAdd = $newProducts->diffKeys($existingProducts);
                foreach ($productsToAdd as $product) {
                    $financialTransaction->financialTransactionsProducts()->create([
                        'product_id' => $product['product_id'],
                        'selling_price' => $product['selling_price'],
                        'installment_price' => $product['installment_price'],
                        'dollar_buying_price' => $product['dollar_buying_price'],
                        'dollar_exchange' => $product['dollar_exchange'],
                        'quantity' => $product['quantity'],

                    ]);
                }
            } else {
                $existingProducts = $financialTransaction->financialTransactionsProducts->keyBy('product_id');
                foreach ($existingProducts as $product) {

                    event(new ProductEvent(['product_id' => $product['product_id']]));

                    $product->delete();
                }
            }
            // Log activity for financial transaction update
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تحديث  فاتورة الشراء  للوكيل: ' . $financialTransaction->agent->name,
                'type_id' => $financialTransaction->id,
                'type_type' => FinancialTransaction::class,
            ]);


            DB::commit();
            return $this->successResponse('تم تحديث   فاتورة الشراء  بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث  فاتورة الشراء : ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث  فاتورة الشراء .يرجى المحاولة مرة أخرى');
        }
    }


    /**
     * Delete a financial transaction and its associated products.
     *
     * This method safely removes a financial transaction from the database while ensuring
     * associated products are processed before deletion. It logs the deletion event for tracking purposes.
     *
     * @param FinancialTransactions $financialTransactions The financial transaction instance to delete.
     * @return \Illuminate\Http\JsonResponse Response indicating success or failure.
     */
    public function deleteFinancialTransaction(FinancialTransaction $financialTransaction)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Retrieve associated products for this financial transaction
            $products = $financialTransaction->financialTransactionsProducts;

            // Ensure there are products before triggering related events
            if ($products->isNotEmpty()) {
                foreach ($products as $product) {
                    // Dispatch event for each product before deletion
                    event(new ProductEvent(['product_id' => $product->product_id]));
                }
            }
            if ($financialTransaction->type == 'تسديد فاتورة شراء') {

                // Log the deletion action in the system
                ActivitiesLog::create([
                    'user_id'    => $userId,
                    'description' => 'تم حذف تسديد فاتورة شراء  لالوكيل: ' . $financialTransaction->agent->name,
                    'type_id'    => $financialTransaction->id,
                    'type_type'  => FinancialTransaction::class,
                ]);
                // Delete the financial transaction record from the database
                $financialTransaction->delete();

                DB::commit();
                return $this->successResponse('تم حذف تسديد فاتورة الشراء .', 200);
            } else {
                // Log the deletion action in the system
                ActivitiesLog::create([
                    'user_id'    => $userId,
                    'description' => 'تم حذف  فاتورة شراء  لالوكيل: ' . $financialTransaction->agent->name,
                    'type_id'    => $financialTransaction->id,
                    'type_type'  => FinancialTransaction::class,
                ]);
                // Delete the financial transaction record from the database
                $financialTransaction->delete();

                DB::commit();
                return $this->successResponse('تم حذف فاتورة الشراء .', 200);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حذف فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف فاتورة الشراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Create a new financial transaction for payment.
     *
     * This method registers a payment transaction for an agent and updates the balance.
     * It logs the transaction in the `ActivitiesLog` and ensures data consistency using `DB::transaction()`.
     *
     * @param int $id The agent ID.
     * @param array $data Payment details (transaction date, paid amount, description).
     * @return \Illuminate\Http\JsonResponse Response indicating success or failure.
     */
    public function StorePaymentFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            // Retrieve the latest financial transaction for the agent
            $lastTransaction = FinancialTransaction::where('agent_id', $id)->latest()->first();
            $sumamount = optional($lastTransaction)->sum_amount ?? 0;

            // Deduct the paid amount from the total sum
            $sumamount -= ($data["paid_amount"] ?? 0);

            // Create a new financial transaction for payment
            $financialTransaction = FinancialTransaction::create([
                'agent_id'        => $id,
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type'            => 'تسديد فاتورة شراء',
                'paid_amount'     => $data["paid_amount"],
                'description'     => $data["description"] ?? null,
                'sum_amount'      => $sumamount,
                'user_id'         => $userId,
            ]);

            // Log the payment transaction in ActivitiesLog
            ActivitiesLog::create([
                'user_id'    => $userId,
                'description' => 'تم  تسديد فاتورة شؤاء للوكيل: ' . $financialTransaction->agent->name,
                'type_id'    => $financialTransaction->id,
                'type_type'  => FinancialTransaction::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تسجيل تسديد فاتورة شراء  بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تسجيل تسديد فاتورة شراء  : ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تسجيل تسديد فاتورة شرا، يرجى المحاولة مرة أخرى.');
        }
    }
    public function UpdatePaymentFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            // Retrieve the financial transaction
            $financialTransaction = FinancialTransaction::findOrFail($id);

            // Retrieve the latest transaction for the agent that is newer than the current one
            $lastTransaction = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                ->where('id', ">", $id)
                ->latest()
                ->first();

            // Calculate the sum amount after deducting the new paid amount
            $sumamount = optional($lastTransaction)->sum_amount ?? 0;
            $sumamount -= ($financialTransaction->paid_amount - ($data["paid_amount"] ?? 0));

            // Update the financial transaction details
            $financialTransaction->update([
                'transaction_date' => $data["transaction_date"] ??$financialTransaction->transaction_date,
                'paid_amount'      => $data["paid_amount"] ??$financialTransaction->paid_amount,
                'description'      => $data["description"] ??$financialTransaction->description,
                'sum_amount'       => $sumamount,
            ]);

            // Log the payment transaction in ActivitiesLog
            ActivitiesLog::create([
                'user_id'    => $userId,
                'description' => 'تم تحديث تسديد فاتورة شراء للوكيل: ' . $financialTransaction->agent->name,
                'type_id'    => $financialTransaction->id,
                'type_type'  => FinancialTransaction::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تحديث تسديد فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث  تسديد فاتورة شرا : ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث  تسديد فاتورة شرا  يرجى المحاولة مرة أخرى.');
        }
    }

}
