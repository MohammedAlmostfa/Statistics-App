<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use Exception;
use Illuminate\Support\Facades\Log;

class InstallmentPaymentService
{
    /**
     * Create a new installment payment.
     *
     * @param array $data Payment data including amount.  It is expected that this data has ALREADY been validated.
     * @param string $id The ID of the Installment to create the payment for.  The service will fetch the Installment.
     * @return array An array containing the status, message, and optionally the created payment.
     */
    public function createInstallmentPayment(array $data, $id): array
    {
        try {
            // Fetch the Installment model using the provided ID, and eager load the necessary relationships.
            //  This ensures that receiptProduct and product are available for any calculations or logic within this service.
            $installment = Installment::with('receiptProduct.product')->findOrFail($id);

            // Create the InstallmentPayment.  We assume the data is valid at this point.
            $payment = $installment->installmentPayments()->create([
                'payment_date' => $data['payment_date'], // Use the payment_date from the validated data.
                'amount' => $data['amount'],             // Use the amount from the validated data.
                'status' => 0,                           // Set the initial status of the payment.
            ]);

            // Return a success response.  Include the newly created payment data.
            return [
                'status' => 201, // Use 201 Created for successful resource creation.
                'message' => 'تم تسجيل دفعة القسط بنجاح',
                'payment' => $payment, // Return the created payment object.
            ];
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process (e.g., database errors).
            Log::error('Error creating installment payment: ' . $e->getMessage(), [
                'installment_id' => $installment->id ?? null, // Log the Installment ID if available.
                'amount' => $data['amount'] ?? null,             // Log the amount from the data.
                'trace' => $e->getTraceAsString(),           // Log the stack trace for debugging.
            ]);

            // Return an error response.
            return [
                'status' => 500, // Use 500 Internal Server Error for server-side errors.
                'message' => 'حدث خطأ أثناء تسديد القسط، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
