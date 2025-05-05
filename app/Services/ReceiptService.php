<?php

namespace App\Services;

use App\Models\Customer;
use Exception;
use App\Models\Receipt;
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
                'data'    => $receipts, // Corrected syntax
            ];
        } catch (\Exception $e) {
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
                'message' => 'تم استرجاع جميع فواتير المستخدم بنجاح',
                'data'    => $receipts,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getCustomerReceipt: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء استرجاع الفواتير.',
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
            $this->storeReceiptProducts($receipt, $data['products'], $data['type'], $data['receipt_date'] ?? now());

            DB::commit();

            return [
                'status' => 200,
                'message' => 'تم إنشاء الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in createReceipt: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة.',
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

    protected function storeReceiptProducts($receipt, array $products, $type, $receiptDate)
    {
        foreach ($products as $productData) {
            $receiptProduct = $receipt->receiptProducts()->create([
                'product_id'  => $productData['product_id'],
                'description' => $productData['description'] ?? null,
                'quantity'    => $productData['quantity'] ?? 1,
            ]);

            if ($type === 'اقساط') {
                $this->createInstallment($receiptProduct, $productData, $receiptDate);
            }
        }
    }

    protected function createInstallment($receiptProduct, $productData, $receiptDate)
    {


        $installment = $receiptProduct->installment()->create([
            'pay_cont'         => $productData['pay_cont'],
                  'first_pay'         => $productData['amount'],
            'installment'      => $productData['installment'],
            'installment_type' => $productData['installment_type']
        ]);


    }
    //_________________________________________________________________________________________________________________________________________________________________________________________________________________
    //_________________________________________________________________________________________________________________________________________________________________________________________________________________
    //_________________________________________________________________________________________________________________________________________________________________________________________________________________
    //_________________________________________________________________________________________________________________________________________________________________________________________________________________

    /**
     * Update an existing receipt with its products and installments.
     */
    // public function updateReceiptWithProducts(Receipt $receipt, $data)
    // {
    //     DB::beginTransaction();


    //     try {
    //         $this->updateReceipt($receipt, $data);
    //         if (!empty($data['products'])) {
    //             Log::error('13s: ');

    //             $this->updateReceiptProducts($receipt, $data['products'], $data['type'] ?? null, $data['receipt_date'] ?? null);
    //         }

    //         DB::commit();

    //         return [
    //             'status' => 200,
    //             'message' => 'تم تحديث الفاتورة بنجاح.',

    //         ];
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error in updateReceiptWithProducts: ' . $e->getMessage());

    //         return [
    //             'status' => 500,
    //             'message' => 'حدث خطأ أثناء تحديث الفاتورة.',
    //         ];
    //     }
    // }

    // protected function updateReceipt(Receipt $receipt, array $data)
    // {

    //     $receipt->update([
    //         'customer_id'    => $data['customer_id'] ?? $receipt->customer_id,
    //         'type'           => $data['type'] ?? $receipt->type,
    //         'total_price'    => $data['total_price'] ?? $receipt->total_price,
    //         'notes'          => $data['notes'] ?? $receipt->notes,
    //         'receipt_date'   => $data['receipt_date'] ?? $receipt->receipt_date,
    //     ]);


    // }

    // protected function updateReceiptProducts($receipt, array $products, $type, $receiptDate)
    // {
    //     $existingProductIds = collect($products)->pluck('product_id')->toArray();
    //     $receipt->receiptProducts()->whereNotIn('product_id', $existingProductIds)->delete();


    //     foreach ($products as $productData) {
    //         $receiptProduct = $receipt->receiptProducts()->updateOrCreate(
    //             ['product_id' => $productData['product_id']],
    //             [
    //                 'description' => $productData['description'] ?? null,
    //                 'quantity'    => $productData['quantity'] ?? 1,
    //             ]
    //         );

    //         if ($type === 'اقساط') {
    //             $this->updateInstallment($receiptProduct, $productData, $receiptDate);
    //         }
    //         if ($type === 'نقدي') {
    //             if ($receiptProduct->installment) {
    //                 $receiptProduct->installment->delete();
    //             }
    //         }


    //     }
    // }

    // protected function updateInstallment($receiptProduct, $productData, $receiptDate)
    // {
    //     $typeValue = array_search($productData['installment_type'], \App\Models\Installment::TYPE_MAP);
    //     if ($typeValue === false) {
    //         throw new Exception("نوع القسط غير صالح: " . $productData['installment_type']);
    //     }

    //     $installment = $receiptProduct->installment;

    //     if ($installment) {
    //         $installment->update([
    //             'pay_cont'         => $productData['pay_cont'],
    //             'installment'      => $productData['installment'],
    //             'installment_type' => $typeValue,
    //         ]);
    //     } else {
    //         $installment = $receiptProduct->installment()->create([
    //             'pay_cont'         => $productData['pay_cont'],
    //             'installment'      => $productData['installment'],
    //             'installment_type' => $typeValue,
    //         ]);
    //     }

    //     $installment->installmentPayments()->updateOrCreate(
    //         ['payment_date' => $receiptDate],
    //         [
    //             'amount' => $productData['amount'],
    //             'status' => 0,
    //         ]
    //     );
    // }

    /**
     * Delete a receipt and its related data.
     */
    public function deleteReceipt(Receipt $receipt)
    {
        try {
            $receipt->delete();

            return [
                'status' => 200,
                'message' => 'تم حذف الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error in deleteReceipt: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
