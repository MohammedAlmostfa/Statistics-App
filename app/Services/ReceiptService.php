<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use App\Models\Receipt;

use App\Events\ReceiptCreated;
use App\Models\ReceiptProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReceiptService
{


    public function getAllReceipt()
    {
        try {
            $receipts = Receipt::with([
                'user' => function ($q) {
                    $q->select('id', 'name');
                },
                'customer' => function ($q) {
                    $q->select('id', 'name');
                }
            ])->paginate(10);

            return [
                'status'  => 200,
                'message' => 'تم استرجاع جميع الفواتير بنجاح',
                'data'    => $receipts,
            ];
        } catch (Exception $e) {
            Log::error('Error in getAllReceipt: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء استرجاع الفواتير.',
            ];
        }
    }

    public function getCustomerReceipt($id)
    {
        try {

            $receipts = Receipt::with(['user:id,name'])
                ->where('customer_id', $id)
                ->paginate(10);

            return [
                'status'  => 200,
                'message' => 'تم استرجاع جميع فواتير العميل بنجاح',
                'data'    => $receipts,
            ];
        } catch (Exception $e) {
            Log::error('Error in getCustomerReceipt: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء استرجاع فواتير العميل.',
            ];
        }
    }

    /**
     * Create a new receipt with its products and installments.
     */
    public function createReceipt(array $data)
    {
        DB::beginTransaction();

        try {
            $receipt = $this->storeReceipt($data);

            $this->storeReceiptProducts($receipt, $data['products'], $data['type']);
            DB::commit();
            return [
                'status' => 200,
                'message' => 'تم إنشاء الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in createReceipt: ' . $e->getMessage(), ['data' => $data, 'exception' => $e]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة ' ,
            ];
        }
    }

    protected function storeReceipt(array $data)
    {
        return Receipt::create([
            'customer_id'    => $data['customer_id'],
            'receipt_number' => $data['receipt_number'],
            'type'           => $data['type'],
            'total_price'    => $data['total_price'],
            'notes'          => $data['notes'] ?? null,
            'receipt_date'   => $data['receipt_date'] ?? now(),
            'user_id'        => Auth::id(),
        ]);
    }

    protected function storeReceiptProducts(Receipt $receipt, array $products, string $type)
    {


        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $buyingPrice = $product->getCalculatedBuyingPrice();
            $sellingPrice = $product->getSellingPriceForReceiptType($type);
            $receiptProduct = $receipt->receiptProducts()->create([
                'product_id'    => $productData['product_id'],
                'description'   => $productData['description'] ?? null,
                'quantity'      => $productData['quantity'],
                'buying_price'  => $buyingPrice,
                'selling_price' => $sellingPrice,
            ]);

            ReceiptCreated::dispatch($productData['product_id'], $productData['quantity']);

            if ($type === 'اقساط') { // اقتراح: Receipt::TYPE_INSTALLMENT
                // تأكد أن بيانات القسط موجودة في $productData
                if (!isset($productData['pay_cont'], $productData['amount'], $productData['installment'], $productData['installment_type'])) {
                    throw new Exception("بيانات القسط غير مكتملة للمنتج ID: " . $productData['product_id']);
                }
                $this->createInstallment($receiptProduct, $productData);
            }
        }

    }


    protected function createInstallment(ReceiptProduct $receiptProduct, array $productData)
    {
        $receiptProduct->installment()->create([
            'pay_cont'         => $productData['pay_cont'],
            'first_pay'        => $productData['amount'],
            'installment'      => $productData['installment'],
            'installment_type' => $productData['installment_type']
        ]);
    }

    /**
     * Updates an existing Receipt and its associated products.
     *
     * @param Receipt $receipt The receipt instance to update.
     * @param array $data The data for updating the receipt and its products.
     * @return array
     */
    public function updateReceiptWithProducts(Receipt $receipt, array $data)
    {
        DB::beginTransaction();

        try {
            $existingReceiptProducts = $receipt->receiptProducts()->get()->keyBy('product_id');

            $this->updateReceipt($receipt, $data);
            if (!empty($data['products'])) {
                $this->syncReceiptProducts($receipt, $data['products'], $existingReceiptProducts);
            } else {
                // إذا لم يتم توفير منتجات في بيانات التحديث، احذف كل المنتجات الحالية وأقساطها
                foreach ($existingReceiptProducts as $existingReceiptProduct) {
                    if ($receipt->type === 'اقساط') { // اقتراح: Receipt::TYPE_INSTALLMENT
                        $existingReceiptProduct->installment()->delete();
                    }
                    // إرسال حدث لتعديل المخزون بالكمية المعكوسة (إرجاع للمخزن)
                    ReceiptCreated::dispatch($existingReceiptProduct->product_id, -$existingReceiptProduct->quantity);
                }
                $receipt->receiptProducts()->delete();
            }

            // إعادة حساب وتحديث السعر الإجمالي
            $this->updateTotalPrice($receipt);

            DB::commit();

            return [
                'status' => 200,
                'message' => 'تم تحديث الفاتورة بنجاح.',
                'data' => $receipt->load('receiptProducts.installment')
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateReceiptWithProducts: ' . $e->getMessage(), ['receipt_id' => $receipt->id, 'data' => $data, 'exception' => $e]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث الفاتورة: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Updates the main Receipt details.
     */
    protected function updateReceipt(Receipt $receipt, array $data): void
    {
        // الحقول التي أكدت أنها قابلة للتعديل
        $receipt->update([
            'customer_id'  => $data['customer_id'] ?? $receipt->customer_id,
            'notes'        => $data['notes'] ?? $receipt->notes,
            'receipt_date' => $data['receipt_date'] ?? $receipt->receipt_date,
            // 'type' و 'receipt_number' لا يتم تحديثهما هنا بناءً على طلبك
        ]);
    }

    /**
     * Syncs receipt products (adds, updates, removes) and handles related installments.
     */
    protected function syncReceiptProducts(Receipt $receipt, array $productsData, $existingReceiptProducts): void
    {
        $currentProductIds = [];

        foreach ($productsData as $productData) {
            if (!isset($productData['product_id']) || !isset($productData['quantity'])) {
                Log::warning("Skipping product data due to missing product_id or quantity", ['product_data' => $productData]);
                continue;
            }

            $productId = $productData['product_id'];
            $currentProductIds[] = $productId;
            $newQuantity = (int)$productData['quantity'];

            $product = Product::findOrFail($productId);
            $oldReceiptProduct = $existingReceiptProducts->get($productId);
            $oldQuantity = $oldReceiptProduct ? (int)$oldReceiptProduct->quantity : 0;

            $quantityDifference = $newQuantity - $oldQuantity;

            if ($quantityDifference !== 0) {
                ReceiptCreated::dispatch($productId, $quantityDifference);
            }

            $buyingPrice = $product->dolar_buying_price * $product->dollar_exchange;

            $sellingPrice = $product->getSellingPriceForReceiptType($receipt->type);

            $description = $productData['description'] ?? $oldReceiptProduct?->description;

            $receiptProduct = $receipt->receiptProducts()->updateOrCreate(
                ['product_id' => $productId],
                [
                    'description'   => $description,
                    'quantity'      => $newQuantity,
                    'buying_price'  => $buyingPrice,
                    'selling_price' => $sellingPrice,
                ]
            );

            $this->createOrUpdateInstallment($receiptProduct, $productData);

        }

        // حذف المنتجات التي لم تعد موجودة في الطلب الجديد
        $productIdsToRemove = $existingReceiptProducts->keys()->diff($currentProductIds);
        foreach ($productIdsToRemove as $productIdToRemove) {
            $productToRemove = $existingReceiptProducts->get($productIdToRemove);
            {
                ReceiptCreated::dispatch($productToRemove->product_id, -$productToRemove->quantity);
                $productToRemove->delete();
            }
        }
    }

    /**
     * Creates or updates the Installment record for a ReceiptProduct.
     */
    protected function createOrUpdateInstallment(ReceiptProduct $receiptProduct, array $productData): void
    {
        $receiptProduct->installment()->updateOrCreate(
            [],
            [
                'pay_cont'         => $productData['pay_cont'],
                'first_pay'        => $productData['amount'],
                'installment'      => $productData['installment'],
                'installment_type' => $productData['installment_type']
            ]
        );
    }

    /**
     * Recalculates and updates the total price of the receipt.
     */
    protected function updateTotalPrice(Receipt $receipt): void
    {

        $totalPrice = $receipt->receiptProducts()->get()->sum(function ($product) {
            return $product->quantity * $product->selling_price;
        });

        $receipt->update(['total_price' => $totalPrice]);
    }

    /**
     * Delete a receipt and its related data.
     */
    public function deleteReceipt(Receipt $receipt)
    {
        DB::beginTransaction();
        try {

            foreach ($receipt->receiptProducts as $receiptProduct) {
                ReceiptCreated::dispatch($receiptProduct->product_id, -$receiptProduct->quantity); // إعادة الكمية للمخزون

            }


            $receipt->delete();

            DB::commit();

            return [
                'status' => 200,
                'message' => 'تم حذف الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in deleteReceipt: ' . $e->getMessage(), ['receipt_id' => $receipt->id, 'exception' => $e]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }


}
