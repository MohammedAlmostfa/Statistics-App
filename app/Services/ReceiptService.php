<?php

namespace App\Services;

use Exception;
use App\Models\Receipt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReceiptService
{
    /**
     * Create a new receipt.
     *
     * @param array $data Receipt data.
     * @return array Response containing status, message, and created receipt data.
     */
    public function createReceipt(array $data)
    {
        try {
            $userId = Auth::id();

            $receipt = Receipt::create([
                'customer_id' => $data['customer_id'],
                'receipt_id' => $data['receipt_id'],
                'type' => $data['type'],
                'total_amount' => $data['total_amount'],
                'received_amount' => $data['received_amount'],
                'remaining_amount' => $data['remaining_amount'],
                'receipt_date' => $data['receipt_date'] ?? now(),
                'user_id' => $userId,
            ]);

            return [
                'status' => 200,
                'message' => 'تم إنشاء الفاتورة بنجاح.',
                'data' => $receipt,
            ];
        } catch (Exception $e) {
            Log::error('Error in createReceipt: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing receipt.
     *
     * @param array $data Updated receipt data.
     * @param Receipt $receipt Receipt model instance.
     * @return array Response containing status and message.
     */
    public function updateReceipt(array $data, Receipt $receipt)
    {
        try {
            $receipt->update([
                                'receipt_id' => $data['receipt_id'] ?? $receipt->receipt_id,
                'customer_id' => $data['customer_id'] ?? $receipt->customer_id,
                'type' => $data['type'] ?? $receipt->type,
                'total_amount' => $data['total_amount'] ?? $receipt->total_amount,
                'received_amount' => $data['received_amount'] ?? $receipt->received_amount,
                'remaining_amount' => $data['remaining_amount'] ?? $receipt->remaining_amount,
                'receipt_date' => $data['receipt_date'] ?? $receipt->receipt_date,
            ]);

            return [
                'status' => 200,
                'message' => 'تم تحديث الفاتورة بنجاح.',
                'data' => $receipt,
            ];
        } catch (Exception $e) {
            Log::error('Error in updateReceipt: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث الفاتورة، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete a receipt.
     *
     * @param Receipt $receipt Receipt model instance.
     * @return array Response containing status and message.
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
