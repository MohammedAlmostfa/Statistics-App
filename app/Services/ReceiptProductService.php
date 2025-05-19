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
class ReceiptProductService extends Service
{

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
