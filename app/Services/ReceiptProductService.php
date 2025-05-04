<?php

namespace App\Services;

use Exception;
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
            $customer = Customer::with([
                'receipts' => function ($q) {
                    $q->select('id', 'customer_id', 'receipt_number', 'receipt_date', 'type')
                        ->where('type', 'اقساط'); // Filter receipts to only include installment type.
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
                'receipts.receiptProducts.installment.installmentPayments' => function ($q) {
                    $q->select('id', 'installment_id', 'amount'); // Select all installment payments for summation.
                },
            ])->findOrFail($id);

            $receiptProductsData = [];
            // Iterate through each receipt of the customer.
            foreach ($customer->receipts as $receipt) {
                // Iterate through each product in the current receipt.
                foreach ($receipt->receiptProducts as $receiptProduct) {
                    // Calculate the total amount paid for the current receipt product's installment.
                    $amountPaid = $receiptProduct->installment->installmentPayments->sum('amount');

                    // Structure the data for each receipt product.
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
                        'amount_paid' => $amountPaid, // Include the calculated total amount paid.
                    ];
                }
            }

            // Return a successful response with the fetched data.
            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $receiptProductsData,
            ];
        } catch (Exception $e) {
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
