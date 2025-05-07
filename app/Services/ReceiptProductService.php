<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\ReceiptProduct;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CustomerReceiptProduct;

class ReceiptProductService
{

    public function getCustomerReceiptProducts($id)
    {
        try {
            $receipts = Receipt::with([
                'receiptProducts' => function ($q) {
                    $q->select('id', 'receipt_id', 'product_id', 'quantity', 'selling_price');
                },
                'receiptProducts.product' => function ($q) {
                    $q->select('id', 'name', );
                },
                'receiptProducts.installment' => function ($q) {
                    $q->select('id', 'receipt_product_id', 'pay_cont', 'first_pay', 'installment_type', 'installment', 'id');
                },
                'receiptProducts.installment.installmentPayments' => function ($q) {
                    $q->select('id', 'installment_id', 'payment_date', 'amount');
                },
            ])
                ->where('customer_id', $id)
                ->where('type', 'اقساط')
                ->get();

            $formattedProducts = $receipts->flatMap(function ($receipt) {
                return $receipt->receiptProducts->map(function ($receiptProduct) {
                    return new CustomerReceiptProduct($receiptProduct);
                });
            });

            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $formattedProducts,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getCustomerReceiptProducts: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }




    public function getreciptProduct($id)
    {
        try {
            $receiptProducts = ReceiptProduct::with(['product' => function ($q) {
                $q->select('id', 'name', 'selling_price', 'quantity', 'installment_price');
            },'installment'])
                ->where('receipt_id', $id)
                ->get();


            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات للفاتورة بنجاح.',
                'data' => $receiptProducts,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getreciptProduct: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب منتجات الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
