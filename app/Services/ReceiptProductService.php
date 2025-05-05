<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CustomerReceiptProductWithFirstPaymentRemoved;

class ReceiptProductService
{
    /**
     * Get all receipt products for a specific customer, including related data for installment payments (excluding the first payment).
     *
     * @param int $id The ID of the customer.
     * @return array An array containing the status, message, and data (list of receipt products with payment information).
     */
    public function getCustomerReceiptProducts($id)
    {
        try {
            $receipts = Receipt::with([
                'receiptProducts' => function ($q) {
                    $q->select('id', 'receipt_id', 'product_id', 'quantity');
                },
                'receiptProducts.product' => function ($q) {
                    $q->select('id', 'name', 'installment_price');
                },
                  'receiptProducts.installment' => function ($q) {
                      $q->select('id', 'receipt_product_id', 'pay_cont', 'first_pay', 'installment_type', 'installment', 'id');
                  },
                'receiptProducts.installment.installmentPayments' => function ($q) {
                    $q->select('id', 'installment_id', 'payment_date', 'amount');
                },

                'receiptProducts.receipt' => function ($q) {
                    $q->select('id', 'receipt_number', 'receipt_date');
                },
            ])
                ->where('customer_id', $id)
                ->where('type', 'اقساط')
                ->get();

            $formattedProducts = $receipts->flatMap(function ($receipt) {
                return $receipt->receiptProducts->map(function ($receiptProduct) {
                    return new CustomerReceiptProductWithFirstPaymentRemoved($receiptProduct);
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
}
