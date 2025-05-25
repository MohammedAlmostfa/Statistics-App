<?php

namespace App\Services;

use Exception;
use App\Events\ProductEvent;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use App\Events\FinancialTransactionEdit;
use App\Models\FinancialTransactionsProduct;

/**
 * Class FinancialTransactionService
 *
 * Manages the creation, update, retrieval, and deletion of financial transactions.
 * Supports product association, activity logging, event dispatching, and transactional consistency.
 */
class FinancialTransactionService extends Service
{
    /**
     * Retrieve all products associated with a financial transaction.
     *
     * @param int $id Financial transaction ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetFinancialTransactionsproducts($id)
    {
        try {
            $products = FinancialTransactionsProduct::where('financial_id', $id)
                ->with('product:id,name')
                ->get();

            return $this->successResponse('تم استرجاع منتجات فاتورة الشراء بنجاح.', 200, $products);
        } catch (Exception $e) {
            Log::error('خطأ أثناء استرجاع المنتجات المرتبطة بفاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء جلب المنتجات المرتبطة بفاتورة الشراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Store a new financial transaction and associate products.
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function StoreFinancialTransaction($data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $lastTransaction = FinancialTransaction::where('agent_id', $data["agent_id"])->latest()->first();

            $sumamount = optional($lastTransaction)->sum_amount ?? 0;
            $sumamount += ($data["total_amount"] ?? 0) - ($data["discount_amount"] ?? 0) - ($data["paid_amount"] ?? 0);

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

            foreach ($data['products'] as $product) {
                $financialTransaction->financialTransactionsProducts()->create([
                    'product_id' => $product['product_id'],
                    'selling_price' => $product['selling_price'],
                    'installment_price' => $product['installment_price'],
                    'dollar_buying_price' => $product['dollar_buying_price'],
                    'dollar_exchange' => $product['dollar_exchange'],
                    'quantity' => $product['quantity'],
                ]);
                event(new ProductEvent($product));
            }

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
            Log::error('خطأ عام أثناء معالجة فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حفظ فاتورة الشراء يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Update an existing financial transaction and its associated products.
     *
     * @param array $data
     * @param FinancialTransaction $financialTransaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdateFinancialTransaction($data, FinancialTransaction $financialTransaction)
    {
        DB::beginTransaction();
        try {

            if ($financialTransaction->type == 'فاتورة شراء') {
                $userId = Auth::id();

                $last = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                    ->where('id', '<', $financialTransaction->id)
                    ->latest('id')
                    ->first();

                $sumAmount = optional($last)->sum_amount ?? 0;
                $sumAmount += ($data["total_amount"] ?? $financialTransaction->total_amount)
                    - ($data["discount_amount"] ?? $financialTransaction->discount_amount)
                    - ($data["paid_amount"] ?? $financialTransaction->paid_amount);

                $financialTransaction->update([
                    'agent_id' => $data["agent_id"] ?? $financialTransaction->agent_id,
                    'type' => $financialTransaction->type,
                    'transaction_date' => $data["transaction_date"] ?? $financialTransaction->transaction_date,
                    'total_amount' => $data["total_amount"] ?? $financialTransaction->total_amount,
                    'discount_amount' => $data["discount_amount"] ?? $financialTransaction->discount_amount,
                    'paid_amount' => $data["paid_amount"] ?? $financialTransaction->paid_amount,
                    'description' => $data["description"] ?? $financialTransaction->description,
                    'sum_amount' => $sumAmount,
                ]);



                // Process products
                $existing = $financialTransaction->financialTransactionsProducts->keyBy('product_id');
                $new = collect($data['products'] ?? [])->keyBy('product_id');

                // Deletion
                $toDelete = $existing->diffKeys($new);
                foreach ($toDelete as $product) {
                    event(new ProductEvent(['product_id' => $product['product_id']]));
                    $product->delete();
                }

                // Update
                $toUpdate = $existing->intersectByKeys($new);
                foreach ($toUpdate as $product) {
                    $updated = $new[$product->product_id];
                    $quantity = $updated['quantity'] - $product->quantity;
                    $product->update([
                        'selling_price' => $updated['selling_price'] ?? $product->selling_price,
                        'installment_price' => $updated['installment_price'] ?? $product->installment_price,
                        'dollar_buying_price' => $updated['dollar_buying_price'] ?? $product->dollar_buying_price,
                        'dollar_exchange' => $updated['dollar_exchange'] ?? $product->dollar_exchange,
                        'quantity' => $updated['quantity'] ?? $product->quantity,
                    ]);
                    $product->quantity = $quantity;
                    event(new ProductEvent($product->toArray()));
                }

                // Add new
                $toAdd = $new->diffKeys($existing);
                foreach ($toAdd as $product) {
                    $financialTransaction->financialTransactionsProducts()->create($product);
                }
                event(new FinancialTransactionEdit($financialTransaction));

                ActivitiesLog::create([
                    'user_id' => $userId,
                    'description' => 'تم تحديث  فاتورة الشراء  للوكيل: ' . $financialTransaction->agent->name,
                    'type_id' => $financialTransaction->id,
                    'type_type' => FinancialTransaction::class,
                ]);
            } else {
                return $this->errorResponse('حدث خطأ أثناء تحديث فاتورة الشراء، يرجى المحاولة مرة أخرى.');
            }
            DB::commit();
            return $this->successResponse('تم تحديث فاتورة الشراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث فاتورة الشراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Delete a financial transaction and trigger related events.
     *
     * @param FinancialTransaction $financialTransaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFinancialTransaction(FinancialTransaction $financialTransaction)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $products = $financialTransaction->financialTransactionsProducts;
            foreach ($products as $product) {
                event(new ProductEvent(['product_id' => $product->product_id]));
            }


            if ($financialTransaction->type == 'تسديد فاتورة شراء') {
                $description = 'تم حذف تسديد فاتورة شراء للوكيل: ' . $financialTransaction->agent->name;
            } elseif ($financialTransaction->type == 'فاتورة شراء') {
                $description = 'تم حذف فاتورة شراء للوكيل: ' . $financialTransaction->agent->name;
            } else {
                $description = 'تم حذف دين فاتورة شؤا  للوكيل: ' . $financialTransaction->agent->name;
            }


            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => $description,
                'type_id' => $financialTransaction->id,
                'type_type' => FinancialTransaction::class,
            ]);

            event(new FinancialTransactionEdit($financialTransaction, 'delete'));
            $financialTransaction->delete();
            DB::commit();
            return $this->successResponse('تم حذف الفاتورة بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حذف فاتورة الشراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف فاتورة الشراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Store a payment transaction for an agent.
     *
     * @param int $id Agent ID.
     * @param array $data Payment details.
     * @return \Illuminate\Http\JsonResponse
     */
    public function StorePaymentFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $LastFinancialTransaction = FinancialTransaction::where('agent_id', $id)->latest()->first();
            $sumamount = optional($LastFinancialTransaction)->sum_amount ?? 0;
            $sumamount -= ($data["paid_amount"] ?? 0);

            $financialTransaction = FinancialTransaction::create([
                'agent_id' => $id,
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'تسديد فاتورة شراء',
                'paid_amount' => $data["paid_amount"],
                'description' => $data["description"] ?? null,
                'sum_amount' => $sumamount,
                'user_id' => $userId,
            ]);

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تسديد فاتورة شراء للوكيل: ' . $financialTransaction->agent->name,
                'type_id' => $financialTransaction->id,
                'type_type' => FinancialTransaction::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تسجيل تسديد فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تسجيل تسديد فاتورة شراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تسجيل تسديد فاتورة شراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Update an existing payment transaction.
     *
     * @param int $id Transaction ID.
     * @param array $data Updated details.
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdatePaymentFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {

            $financialTransaction = FinancialTransaction::findOrFail($id);
            if ($financialTransaction->type == 'تسديد فاتورة شراء') {
                $userId = Auth::id();

                $LastFinancialTransaction = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                    ->where('id', '<', $id)
                    ->latest()
                    ->first();

                $sumAmount = optional($LastFinancialTransaction)->sum_amount ?? 0;
                $sumAmount -= (($data["paid_amount"] ?? 0));

                $financialTransaction->update([
                    'transaction_date' => $data["transaction_date"] ?? $financialTransaction->transaction_date,
                    'paid_amount' => $data["paid_amount"] ?? $financialTransaction->paid_amount,
                    'description' => $data["description"] ?? $financialTransaction->description,
                    'sum_amount' => $sumAmount,
                ]);

                event(new FinancialTransactionEdit($financialTransaction));


                ActivitiesLog::create([
                    'user_id' => $userId,
                    'description' => 'تم تحديث تسديد فاتورة شراء للوكيل: ' . $financialTransaction->agent->name,
                    'type_id' => $financialTransaction->id,
                    'type_type' => FinancialTransaction::class,
                ]);
            } else {
                return $this->errorResponse('حدث خطأ أثناء تحديث تسديد فاتورة شراء، يرجى المحاولة مرة أخرى.');
            }
            DB::commit();
            return $this->successResponse('تم تحديث تسديد فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث تسديد فاتورة شراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث تسديد فاتورة شراء، يرجى المحاولة مرة أخرى.');
        }
    }






    /**
     * Store a debt transaction for an agent.
     *
     * @param int $id Agent ID.
     * @param array $data Debt details.
     * @return \Illuminate\Http\JsonResponse
     */
    public function StoreDebtFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $LastFinancialTransaction = FinancialTransaction::where('agent_id', $id)->latest()->first();
            $sumAmount = optional($LastFinancialTransaction)->sum_amount ?? 0;
            $sumAmount += ($data["total_amount"] ?? 0);

            $financialTransaction = FinancialTransaction::create([
                'agent_id' => $id,
                'transaction_date' => $data["transaction_date"] ?? now(),
                'type' => 'دين فاتورة شراء',
                'total_amount' => $data["total_amount"],
                'description' => $data["description"] ?? null,
                'sum_amount' => $sumAmount,
                'user_id' => $userId,
            ]);

            ActivitiesLog::create([
                'user_id' => $userId,
                'description' => 'تم تسجيل دين فاتورة شراء للوكيل: ' . $financialTransaction->agent->name,
                'type_id' => $financialTransaction->id,
                'type_type' => FinancialTransaction::class,
            ]);

            DB::commit();
            return $this->successResponse('تم تسجيل دين فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تسجيل دين فاتورة شراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تسجيل دين فاتورة شراء، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Update an existing debt transaction
     *
     * @param int $id Transaction ID.
     * @param array $data Updated debt details.
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdateDebtFinancialTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $financialTransaction = FinancialTransaction::findOrFail($id);
            if ($financialTransaction->type == 'دين فاتورة شراء') {
                // Calculate new sum_amount for future transactions
                $LastFinancialTransaction = FinancialTransaction::where('agent_id', $financialTransaction->agent_id)
                    ->where('id', '<', $id)
                    ->latest()
                    ->first();

                $sumAmount = optional($LastFinancialTransaction)->sum_amount ?? 0;
                $totalAamountBefore = $financialTransaction->total_amount;
                $totalAamount = $data["total_amount"] ?? $totalAamountBefore;

                $sumAmount += ($totalAamount - $totalAamountBefore);

                $financialTransaction->update([
                    'transaction_date' => $data["transaction_date"] ?? $financialTransaction->transaction_date,
                    'total_amount' => $totalAamount,
                    'description' => $data["description"] ?? $financialTransaction->description,
                    'sum_amount' => $sumAmount,
                ]);

                event(new FinancialTransactionEdit($financialTransaction));


                ActivitiesLog::create([
                    'user_id' => $userId,
                    'description' => 'تم تحديث دين فاتورة شراء للوكيل: ' . $financialTransaction->agent->name,
                    'type_id' => $financialTransaction->id,
                    'type_type' => FinancialTransaction::class,
                ]);
            } else {
                return $this->errorResponse('حدث خطأ أثناء تحديث دين فاتورة شراء، يرجى المحاولة مرة أخرى.');
            }
            DB::commit();
            return $this->successResponse('تم تحديث دين فاتورة شراء بنجاح.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث دين فاتورة شراء: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث دين فاتورة شراء، يرجى المحاولة مرة أخرى.');
        }
    }
}
