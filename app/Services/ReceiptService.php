<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ActivitiesLog;
use App\Events\ReceiptCreated;
use App\Models\ReceiptProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReceiptService extends Service
{
    /**
     * Get all receipts with related user and customer info.
     */
    public function getAllReceipt(array $filteringData)
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'receipts_'.$page.'_'.md5(json_encode($filteringData));
            $cacheKeys = Cache::get('all_receipts_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_receipts_keys', $cacheKeys, now()->addHours(120));
            }
            $receipts = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($filteringData) {
                return Receipt::with(['user:id,name', 'customer:id,name'])
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->orderByDesc('receipt_date')->paginate(10);
            });

            return [
                'status'  => 200,
                'message' => 'تم استرجاع جميع الفواتير بنجاح',
                'data'    => $receipts,
            ];
        } catch (\Exception $e) {
            Log::error(' خطأ في استرجاع الفواتير: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => ' حدث خطأ أثناء استرجاع الفواتير.',
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


        $receipt= Receipt::create([
             'customer_id'    => $data['customer_id'],
             'receipt_number' => $data['receipt_number'],
             'type'           => $data['type'],
             'total_price'    => $data['total_price'],
             'notes'          => $data['notes'] ?? null,
             'receipt_date'   => $data['receipt_date'] ?? now(),
             'user_id'        => Auth::id(),
         ]);

        ActivitiesLog::create([
         'user_id'     => Auth::id(),
         'description' => 'تم اضافة فاتورة ذات الرقم : ' . $receipt->receipt_number,
         'type_id'     => $receipt->id,
         'type_type'   => Receipt::class,
        ]);
        return   $receipt ;
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

            event(new ReceiptCreated($productData['product_id'], $productData['quantity']));
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

            $receipt->load('receiptProducts');

            $existingReceiptProducts = $receipt->receiptProducts->keyBy('product_id');

            $this->updateReceipt($receipt, $data);

            $currentProductIds = array_column($data['products'], 'product_id');
            $deletedProductIds = $existingReceiptProducts->keys()->diff($currentProductIds);
            $addedProductIds = collect($currentProductIds)->diff($existingReceiptProducts->keys());
            $updatedProductIds = $existingReceiptProducts->keys()->intersect($currentProductIds);


            foreach ($deletedProductIds as $productIdToRemove) {
                $productToRemove = $existingReceiptProducts->get($productIdToRemove);

                event(new ReceiptCreated($productToRemove->product_id, -$productToRemove->quantity));

                $productToRemove->delete();
            }

            foreach ($data['products'] as $productData) {
                $productId = $productData['product_id'];

                if ($addedProductIds->contains($productId)) {

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


                    event(new ReceiptCreated($productId, (int)$productData['quantity']));
                    if ($receiptType === 'اقساط') {
                        $this->createInstallment($receiptProduct, $productData);
                    }
                } elseif ($updatedProductIds->contains($productId)) {

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

                        event(new ReceiptCreated($productId, $quantityDifference));



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
                'message' => 'حدث خطأ أثناء تحديث الفاتورة.',
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
        ActivitiesLog::create([
           'user_id'     => Auth::id(),
           'description' => 'تم تعديل فاتورة ذات الرقم : ' . $receipt->receipt_number,
           'type_id'     => $receipt->id,
           'type_type'   => Receipt::class,
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
            $receipt->load('receiptProducts');

            if ($receipt->receiptProducts->isNotEmpty()) {
                foreach ($receipt->receiptProducts as $receiptProduct) {

                    event(new ReceiptCreated($receiptProduct->product_id, -$receiptProduct->quantity));

                }
            }
            $receipt->delete();

            ActivitiesLog::create([
                'user_id'     => Auth::id(),
                'description' => 'تم حذف فاتورذات الرقم : ' . $receipt->receipt_number,
                'type_id'     => $receipt->id,
                'type_type'   => Receipt::class,
            ]);



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
