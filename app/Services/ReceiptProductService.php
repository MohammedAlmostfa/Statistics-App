<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CustomerReceiptProductResource; // Renamed to singular

class ReceiptProductService
{
    public function getCustomerReceiptProducts($id)
    {
        try {
            $customer = Customer::with([
                'receipts' => function ($q) {
                    $q->select('id', 'customer_id', 'receipt_number', 'receipt_date', 'type')
                        ->where('type', 'اقساط');
                },
                'receipts.receiptProducts' => function ($q) {
                    $q->select('id', 'receipt_id', 'product_id', 'quantity');
                },
                'receipts.receiptProducts.product' => function ($q) {
                    $q->select('id', 'name', 'installment_price');
                },
                'receipts.receiptProducts.installment' => function ($q) {
                    $q->select('id', 'receipt_product_id', 'pay_cont', 'installment_type', 'installment');
                },
                'receipts.receiptProducts.installment.firstInstallmentPayment' => function ($q) {
                    $q->select('id', 'installment_id', 'amount');
                },
            ])->findOrFail($id);

            $receiptProductsData = [];
            foreach ($customer->receipts as $receipt) {
                foreach ($receipt->receiptProducts as $receiptProduct) {
                    $receiptProductsData[] = [
                        'receipt_number' => $receipt->receipt_number,
                        'receipt_date' => $receipt->receipt_date,
                        'quantity' => $receiptProduct->quantity,
                        'product_name' => $receiptProduct->product->name,
                        'product_price' => $receiptProduct->product->installment_price,
                        'pay_cont' => $receiptProduct->installment->pay_cont,
                        'installment_type' => $receiptProduct->installment->installment_type,
                        'installment' => $receiptProduct->installment->installment,
                        'first_pay' => $receiptProduct->installment->firstInstallmentPayment ? $receiptProduct->installment->firstInstallmentPayment->amount : null,
                    ];
                }
            }

            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $receiptProductsData,
            ];
        } catch (Exception $e) {
            Log::error('Error in getCustomerReceiptProducts: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
