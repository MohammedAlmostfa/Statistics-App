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

            $formattedProducts = [];

            foreach ($receipts as $receipt) {
                foreach ($receipt->receiptProducts as $receiptProduct) {
                    $installment = $receiptProduct->installment;
                    $formattedProducts[] = [
                        'receipt_id' => $receipt->id,
                        'receipt_number' => $receipt->receipt_number,
                        'receipt_date' => $receipt->receipt_date->format('Y-m-d'),
                        'product_id' => $receiptProduct->product_id,
                        'product_name' => $receiptProduct->product->name,
                        'quantity' => $receiptProduct->quantity,
                        'product_price' => $receiptProduct->product->installment_price,
                        'pay_cont' => $installment ? $installment->pay_cont : null,
                        'installment_id' => $installment ? $installment->id : null,
                        'installment_type' => $installment ? $installment->installment_type : null,
                        'installment_amount' => $installment ? $installment->installment : null,
                        'first_payment' => ($installment && $installment->firstInstallmentPayment) ? $installment->firstInstallmentPayment->amount : null,
                        'payments' => ($installment && $installment->installmentPayments) ? $installment->installmentPayments->map(function ($payment) {
                            return [
                                'id' => $payment->id,
                                'payment_date' => $payment->payment_date,
                                'amount' => $payment->amount,
                            ];
                        })->toArray() : [],
                    ];
                }
            }

            // Return a successful response with the fetched and formatted data.
            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $formattedProducts,
            ];

        } catch (\Exception $e) {
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
