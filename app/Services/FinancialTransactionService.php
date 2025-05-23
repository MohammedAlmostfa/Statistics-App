<?php

namespace App\Services;

use Exception;
use App\Events\ProductEvent;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialTransactions;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

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
            $lastfinancialTransactions = FinancialTransactions::where('agent_id', $data["agent_id"])->latest()->first();

            // Calculate sum_amount safely, ensuring previous transactions exist
            $sumamount = optional($lastfinancialTransactions)->sum_amount ?? 0;
            $sumamount += ($data["total_amount"] ?? 0) - ($data["discount_amount"] ?? 0) - ($data["paid_amount"] ?? 0);

            // Creating a new financial transaction record
            $financialTransactions = FinancialTransactions::create([
                'agent_id' => $data["agent_id"],
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'فاتورة شراء',
                'total_amount' => $data["total_amount"],
                'discount_amount' => $data["discount_amount"],
                'paid_amount' => $data["paid_amount"],
                'description' => $data["description"],
                'sum_amount'=>$sumamount,
                'user_id' => $userId,
            ]);

            // Adding products to the transaction
            $products = $data['products'];
            foreach ($products as $product) {
                $financialTransactions->financialTransactionsProducts()->create([
                    'product_id' => $product['product_id'],
                    'selling_price' => $product['selling_price'],
                    'installment_price' => $product['installment_price'],
                    'dollar_buying_price' => $product['dollar_buying_price'],
                    'dollar_exchange' => $product['dollar_exchange'],
                    'quantity' => $product['quantity'],
                ]);
                $productData = Product::findOrFail($product['product_id']);

                // Trigger product event for tracking changes
                event(new ProductEvent($product));
            }

            // Log activity for financial transaction creation
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تمت إضافة  فاتورة شراء  جديدة للوكيل: ' . $financialTransactions->agent->name,
                'type_id' => $financialTransactions->id,
                'type_type' => FinancialTransactions::class,
            ]);

            DB::commit();
            return $this->successResponse('تم إنشاء  فاتورة شراء    بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ عام أثناء معالجة   فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حفظ  فاتورة الشراء .');
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
    public function UpdateFinancialTransaction($data, FinancialTransactions $financialTransactions)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Update financial transaction details
            $financialTransactions->update([
                'agent_id' => $data["agent_id"] ?? $financialTransactions->agent_id,
                'type' => $financialTransactions->type,
                'transaction_date' => $data["transaction_date"] ?? $financialTransactions->transaction_date,
                'total_amount' => $data["total_amount"] ?? $financialTransactions->total_amount,
                'discount_amount' => $data["discount_amount"] ?? $financialTransactions->discount_amount,
                'paid_amount' => $data["paid_amount"] ?? $financialTransactions->paid_amount,
                'description' => $data["description"] ?? $financialTransactions->description,

            ]);

            // Process product updates
            if (!empty($data['products'])) {
                // Retrieve existing products linked to the transaction
                $existingProducts = $financialTransactions->financialTransactionsProducts->keyBy('product_id');
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
                    $financialTransactions->financialTransactionsProducts()->create([
                        'product_id' => $product['product_id'],
                        'selling_price' => $product['selling_price'],
                        'installment_price' => $product['installment_price'],
                        'dollar_buying_price' => $product['dollar_buying_price'],
                        'dollar_exchange' => $product['dollar_exchange'],
                        'quantity' => $product['quantity'],

                    ]);
                }

                // Log activity for financial transaction update
                ActivitiesLog::create([
                    'user_id' => $userId,
                    'description' => 'تم تحديث  فاتورة الشراء  للوكيل: ' . $financialTransactions->agent->name,
                    'type_id' => $financialTransactions->id,
                    'type_type' => FinancialTransactions::class,
                ]);
            }

            DB::commit();
            return $this->successResponse('تم تحديث   فاتورة الشراء  بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث  فاتورة الشراء : ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث  فاتورة الشراء .');
        }
    }



    public function deleteFinancialTransaction(FinancialTransactions $financialTransactions)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // Retrieve associated products
            $products = $financialTransactions->financialTransactionsProducts;

            // Ensure there are products before processing events
            if ($products->isNotEmpty()) {
                foreach ($products as $product) {
                    event(new ProductEvent(['product_id' => $product->product_id]));
                }
            }

            // Delete the financial transaction
            $financialTransactions->delete();

            // Log activity for financial transaction deletion
            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم حذف المعاملة  فاتورة الشراء للوكيل: ' . $financialTransactions->agent->name,
                'type_id' => $financialTransactions->id,
                'type_type' => FinancialTransactions::class,
            ]);

            DB::commit();
            return $this->successResponse('تم حذف  فاتورة الشراء  والمنتجات المرتبطة بها بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حذف  فاتورة الشراء : ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف  فاتورة الشراء .');
        }
    }

    public function createPayment($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $lastTransaction = FinancialTransactions::where('agent_id', $data["agent_id"])->latest()->first();
            $sumamount = optional($lastTransaction)->sum_amount ?? 0;
            $sumamount -= ($data["paid_amount"] ?? 0);


            $financialTransactions = FinancialTransactions::create([
                'agent_id' => $data["agent_id"],
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'تسديد فاتورة شراء',
                'paid_amount' => $data["paid_amount"],
                'description' => $data["description"],
                'sum_amount' => $sumamount,
            ]);


            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم إضافة عملية دفع للوكيل: ' . $financialTransactions->agent->name,
                'type_id' => $financialTransactions->id,
                'type_type' => FinancialTransactions::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تسجيل عملية الدفع بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تسجيل عملية الدفع: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تسجيل عملية الدفع، يرجى المحاولة مرة أخرى.');
        }
    }



}
