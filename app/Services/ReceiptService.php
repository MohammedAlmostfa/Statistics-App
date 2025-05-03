<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReceiptService
{
    /**
     * Create a new receipt along with products and installments (if applicable).
     *
     * This method handles the creation of a receipt, adding products to it,
     * and managing installment details if the receipt type is 'اقساط'.
     * If any part of the operation fails, the entire transaction is rolled back.
     *
     * @param array $data Receipt data, including customer details, products, and installment info.
     * @return array Response containing status, message, and created receipt data.
     */
    public function createReceipt(array $data)
    {
        DB::beginTransaction(); // Start the transaction to ensure all changes are applied together

        try {
            $userId = Auth::id(); // Get the authenticated user's ID

            // Create the receipt record in the database
            $receipt = Receipt::create([
                'customer_id'    => $data['customer_id'],
                'receipt_number' => $data['receipt_number'],
                'type'           => $data['type'],
                'total_price'    => $data['total_price'],
                'notes'          => $data['notes'] ?? null,
                'receipt_date'   => $data['receipt_date'] ?? now(),
                'user_id'        => $userId,
            ]);

            // Iterate through the products in the request and create the associated records
            foreach ($data['products'] as $productData) {

                // Create a record for each product in the receipt
                $receiptProduct = $receipt->receiptProducts()->create([
                    'product_id'  => $productData['product_id'],
                    'description'=> $productData['description'] ?? null,
                    'quantity'   => $productData['quantity'] ?? 1,
                ]);

                // Handle installment creation if the receipt type is 'اقساط'
                if ($data['type'] === 'اقساط') {
                    // Convert the installment type from text to the numeric value
                    $installmentTypeValue = array_search($productData['installment_type'], \App\Models\Installment::TYPE_MAP);

                    // Validate the installment type
                    if ($installmentTypeValue === false) {
                        throw new \Exception("نوع القسط غير صالح: " . $productData['installment_type']);
                    }

                    // Create the installment record for the product
                    $installment = $receiptProduct->installment()->create([
                        'pay_cont'         => $productData['pay_cont'],
                        'installment'      => $productData['installment'],
                        'installment_type' => $installmentTypeValue,
                    ]);

                    // If the installment is created, create the associated installment payment
                    if ($installment) {
                        $installment->InstallmentPayments()->create([
                            'payment_date' => $productData['receipt_date'] ?? now(),
                            'amount'       => $productData['amount'],
                            'status'       => 0, // Status can be updated as per payment conditions
                        ]);
                    }
                }
            }

            DB::commit(); // Commit the transaction if everything is successful

            // Return success response with receipt data
            return [
                'status' => 200,
                'message' => 'تم إنشاء الفاتورة بنجاح.',
                'data' => $receipt,
            ];
        } catch (\Exception $e) {
            // Rollback the transaction in case of any errors
            DB::rollback();

            // Log the error for debugging purposes
            Log::error('Error in createReceipt: ' . $e->getMessage());

            // Return error response
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing receipt.
     *
     * This method updates the receipt's details like receipt number, total price, and more.
     * It will only update the provided fields.
     *
     * @param array $data Updated receipt data.
     * @param Receipt $receipt Receipt model instance to be updated.
     * @return array Response containing status and message.
     */
    public function updateReceipt(array $data, Receipt $receipt)
    {
        try {
            // Update the receipt with the new data or retain the existing values
            $receipt->update([
                'receipt_number' => $data['receipt_number'] ?? $receipt->receipt_number,
                'customer_id' => $data['customer_id'] ?? $receipt->customer_id,
                'type' => $data['type'] ?? $receipt->type,
                'total_price' => $data['total_price'] ?? $receipt->total_price,
                'notes' => $data['notes'] ?? $receipt->notes,
                'receipt_date' => $data['receipt_date'] ?? $receipt->receipt_date,
            ]);

            // Return success response
            return [
                'status' => 200,
                'message' => 'تم تحديث الفاتورة بنجاح.',
                'data' => $receipt,
            ];
        } catch (Exception $e) {
            // Log the error if any
            Log::error('Error in updateReceipt: ' . $e->getMessage());

            // Return error response
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete a receipt from the database.
     *
     * This method deletes the specified receipt and all its associated products and installments.
     * It ensures the deletion of all related data when the receipt is deleted.
     *
     * @param Receipt $receipt Receipt model instance to be deleted.
     * @return array Response containing status and message.
     */
    public function deleteReceipt(Receipt $receipt)
    {
        try {
            // Delete the receipt along with its associated data
            $receipt->delete();

            // Return success response
            return [
                'status' => 200,
                'message' => 'تم حذف الفاتورة بنجاح.',
            ];
        } catch (Exception $e) {
            // Log the error if any
            Log::error('Error in deleteReceipt: ' . $e->getMessage());

            // Return error response
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
