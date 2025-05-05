<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class ReceiptProductService
{
    /**
     * Get all receipt products for a specific customer, including related data for installment payments.
     *
     * @param int $id The ID of the customer.
     * @return array An array containing the status, message, and data (list of receipt products with payment information).
     */
    public function getCustomerReceiptProducts($id)
    {
        try {
            // Eager load all necessary relationships to optimize database queries.
            $receipts = Receipt::with([
                'receiptProducts' => function ($q) {
                    $q->select('id', 'receipt_id', 'product_id', 'quantity');
                },
                'receiptProducts.product' => function ($q) {
                    $q->select('id', 'name', 'installment_price');
                },
                'receiptProducts.installment' => function ($q) {
                    $q->select('id', 'receipt_product_id', 'pay_cont', 'installment_type', 'installment');
                },
                'receiptProducts.installment.firstInstallmentPayment' => function ($q) {
                    $q->select('id', 'installment_id', 'amount');
                },
                'receiptProducts.installment.installmentPayments' => function ($q) {
                    $q->select('id', 'installment_id', 'payment_date', 'amount');
                },

            ])
            ->where('customer_id', $id)
            ->where('type', 'اقساط')
            ->get();

            // Return a successful response with the fetched data.
            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $receipts,
            ];
        } catch (\Exception $e) { // Corrected the namespace for Exception
            // Log any errors that occur during the process.
            Log::error('Error in getCustomerReceiptProducts: ' . $e->getMessage());
            // Return an error response.
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
