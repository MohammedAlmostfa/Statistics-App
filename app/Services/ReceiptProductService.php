<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\ReceiptProduct;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CustomerReceiptProduct;

/**
 * Service class for managing receipt products.
 * Includes methods for retrieving customer receipt products and specific receipt products.
 */
class ReceiptProductService
{
    /**
     * Retrieve all receipt products for a specific customer, including installment details.
     *
     * This method fetches receipts with products, installment details, and installment payments for a specific customer.
     * The data is returned in a formatted structure using a resource.
     *
     * @param int $id Customer ID to filter the receipts.
     * @return array Structured response with success or error message in Arabic.
     */
    public function getCustomerReceiptProducts($id)
    {
        try {

            $receipts = Receipt::with([
                'receiptProducts' => function ($q) {
                    $q->select('id', 'receipt_id', 'product_id', 'quantity', 'selling_price');
                },
                'receiptProducts.product' => function ($q) {
                    $q->select('id', 'name');
                },
                'receiptProducts.installment' => function ($q) {
                    $q->select('id', 'receipt_product_id', 'pay_cont', 'first_pay', 'installment_type', 'status', 'installment', 'id');
                },
                'receiptProducts.installment.installmentPayments' => function ($q) {
                    $q->select('id', 'installment_id', 'payment_date', 'amount');
                },
            ])
                ->where('customer_id', $id)  // Filter receipts by the customer ID
                ->orderByDesc('receipt_date')
                ->where('type', 'اقساط')     // Filter only installment type receipts
                ->get();

            // Format the data by flattening it and converting it into the appropriate resource
            $formattedProducts = $receipts->flatMap(function ($receipt) {
                return $receipt->receiptProducts->map(function ($receiptProduct) {
                    return new CustomerReceiptProduct($receiptProduct);  // Transform each receipt product using a resource
                });
            });

            // Return the response with formatted data
            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $formattedProducts,
            ];
        } catch (\Exception $e) {
            // Log any errors and return a failure response
            Log::error('Error in getCustomerReceiptProducts: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Retrieve all products for a specific receipt, including product and installment details.
     *
     * This method retrieves the receipt products for a specific receipt ID, including product information (name, price, etc.)
     * and the related installment details.
     *
     * @param int $id Receipt ID to filter the receipt products.
     * @return array Structured response with success or error message in Arabic.
     */
    public function getreciptProduct($id)
    {
        try {
            // Retrieve the receipt products with product and installment details for the given receipt ID
            $receiptProducts = ReceiptProduct::with([
                'product' => function ($q) {
                    $q->select('id', 'name', 'selling_price', 'quantity', 'installment_price'); // Select specific fields for product
                },
                'installment'
            ])
                ->where('receipt_id', $id)  // Filter by receipt ID
                ->get();

            // Return the success response with the data
            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات للفاتورة بنجاح.',
                'data' => $receiptProducts,
            ];
        } catch (\Exception $e) {
            // Log the error and return failure response
            Log::error('Error in getreciptProduct: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب منتجات الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
