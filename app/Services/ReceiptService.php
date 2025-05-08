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
use Illuminate\Support\Facades\Cache;

class ReceiptService
{
    /**
     * Get all receipts with related user and customer info.
     */
    public function getAllReceipt()
    {
        try {
            $cacheKey = 'receipts';

            $receipts = Cache::remember($cacheKey, now()->addMinutes(15), function () {
                return Receipt::with([
                    'user:id,name',
                    'customer:id,name'
                ])->paginate(10);
            });

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

    /**
     * Get receipts for a specific customer.
     */
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
     * Create a new receipt and its related products (and installments if needed).
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
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة ',
            ];
        }
    }

    /**
     * Store receipt main record.
     */
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

    /**
     * Store all products related to a receipt.
     */
    protected function storeReceiptProducts(Receipt $receipt, array $products, string $type)
    {
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);

            $buyingPrice = $product->getCalculatedBuyingPrice();
            $sellingPrice = $product->getSellingPriceForReceiptType($type);

            $receiptProduct = $receipt->receiptProducts()->create([
                'product_id'     => $productData['product_id'],
                'description'    => $productData['description'] ?? null,
                'quantity'       => $productData['quantity'],
                'buying_price'   => $buyingPrice,
                'selling_price'  => $sellingPrice,
            ]);

            // Update inventory via event
            ReceiptCreated::dispatch($productData['product_id'], $productData['quantity']);

            // Handle installments if type is "اقساط"
            if ($type === 'اقساط') {
                if (!isset($productData['pay_cont'], $productData['first_pay'], $productData['installment'], $productData['installment_type'])) {
                    throw new Exception("بيانات القسط غير مكتملة للمنتج ID: " . $productData['product_id']);
                }

                $this->createInstallment($receiptProduct, $productData);
            }
        }
    }

    /**
     * Create a new installment record.
     */
    protected function createInstallment(ReceiptProduct $receiptProduct, array $productData)
    {
        $receiptProduct->installment()->create([
            'pay_cont'         => $productData['pay_cont'],
            'first_pay'        => $productData['first_pay'],
            'installment'      => $productData['installment'],
            'installment_type' => $productData['installment_type']
        ]);
    }

    /**
     * Update receipt and sync its products.
     */
    public function updateReceiptWithProducts(Receipt $receipt, array $data)
    {
        DB::beginTransaction();

        try {
            $existingReceiptProducts = $receipt->receiptProducts()->get()->keyBy('product_id');

            $this->updateReceipt($receipt, $data);

            $currentProductIds = array_column($data['products'], 'product_id');
            $deletedProductIds = $existingReceiptProducts->keys()->diff($currentProductIds);
            $addedProductIds = collect($currentProductIds)->diff($existingReceiptProducts->keys());
            $updatedProductIds = $existingReceiptProducts->keys()->intersect($currentProductIds);

            // Remove deleted products
            foreach ($deletedProductIds as $productIdToRemove) {
                $productToRemove = $existingReceiptProducts->get($productIdToRemove);
                ReceiptCreated::dispatch($productToRemove->product_id, -$productToRemove->quantity);
                $productToRemove->delete();
            }

            foreach ($data['products'] as $productData) {
                $productId = $productData['product_id'];

                if ($addedProductIds->contains($productId)) {
                    // Add new product
                    $product = Product::findOrFail($productId);
                    $receiptType = $receipt->type;
                    $buyingPrice = $product->getCalculatedBuyingPrice();
                    $sellingPrice = $product->getSellingPriceForReceiptType($receiptType);

                    $receiptProduct = $receipt->receiptProducts()->create([
                        'receipt_id'    => $receipt->id,
                        'product_id'    => $productId,
                        'description'   => $productData['description'] ?? null,
                        'quantity'      => (int)$productData['quantity'],
                        'buying_price'  => $buyingPrice,
                        'selling_price' => $sellingPrice,
                    ]);

                    ReceiptCreated::dispatch($productId, (int)$productData['quantity']);

                    if ($receiptType === 'اقساط') {
                        $this->createInstallment($receiptProduct, $productData);
                    }

                } elseif ($updatedProductIds->contains($productId)) {
                    // Update product
                    $receiptProduct = $existingReceiptProducts->get($productId);
                    $oldQuantity = (int)$receiptProduct->quantity;
                    $newQuantity = (int)$productData['quantity'];
                    $description = $productData['description'] ?? $receiptProduct->description;

                    $receiptProduct->update([
                        'quantity'    => $newQuantity ?? $receiptProduct->quantity,
                        'description' => $description ?? $receiptProduct->description,
                    ]);

                    $quantityDifference = $newQuantity - $oldQuantity;
                    if ($quantityDifference !== 0) {
                        ReceiptCreated::dispatch($productId, $quantityDifference);
                    }

                    if ($receipt->type === 'اقساط') {
                        $this->createOrUpdateInstallment($receiptProduct, $productData);
                    }
                }
            }

            $this->updateTotalPrice($receipt);

            DB::commit();

            return [
                'status' => 200,
                'message' => 'تم تحديث الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateReceiptWithProducts: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'data'       => $data,
                'exception'  => $e
            ]);

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث الفاتورة: ',
            ];
        }
    }

    /**
     * Update receipt base info (not products).
     */
    protected function updateReceipt(Receipt $receipt, array $data): void
    {
        $receipt->update([
            'customer_id'  => $data['customer_id'] ?? $receipt->customer_id,
            'notes'        => $data['notes'] ?? $receipt->notes,
            'receipt_date' => $data['receipt_date'] ?? $receipt->receipt_date,
        ]);
    }

    /**
     * Create or update an installment entry for a product.
     */
    protected function createOrUpdateInstallment(ReceiptProduct $receiptProduct, array $productData): void
    {
        $receiptProduct->installment()->updateOrCreate(
            [],
            [
                'pay_cont'         => $productData['pay_cont'],
                'first_pay'        => $productData['first_pay'],
                'installment'      => $productData['installment'],
                'installment_type' => $productData['installment_type']
            ]
        );
    }

    /**
     * Recalculate total price of the receipt.
     */
    protected function updateTotalPrice(Receipt $receipt): void
    {
        $receipt->load('receiptProducts');

        $totalPrice = $receipt->receiptProducts->sum(function ($receiptProduct) {
            return $receiptProduct->quantity * $receiptProduct->selling_price;
        });

        $receipt->update(['total_price' => $totalPrice]);
    }

    /**
     * Delete receipt and related products/installments.
     */
    public function deleteReceipt(Receipt $receipt)
    {
        DB::beginTransaction();
        try {
            foreach ($receipt->receiptProducts as $receiptProduct) {
                ReceiptCreated::dispatch($receiptProduct->product_id, -$receiptProduct->quantity);
            }

            $receipt->receiptProducts()->delete();
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
