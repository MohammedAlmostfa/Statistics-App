<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Notifications\SendWhatsAppNotification;

/**
 * Handles customer CRUD (Create, Read, Update, Delete) operations.
 * Provides methods for managing customer data and interacting with the database.
 */
class CustomerService
{
    /**
     * Retrieve all customers.
     *
     * @param array|null $filteringData Data for filtering customers.
     * @return array Response containing status, message, and list of customers.
     */
    public function getAllCustomers(array $filteringData = null): array
    {
        try {
            $customers = Customer::query()
                ->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                })
                ->get();

            return $this->successResponse('تم استرجاع العملاء بنجاح', 200, $customers);
        } catch (Exception $e) {
            Log::error('خطأ أثناء استرجاع العملاء: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع العملاء');
        }
    }

    /**
     * Create a new customer.
     *
     * @param array $data Array containing customer details ['name', 'phone', 'notes'].
     * @return array Response containing status, message, and created customer data.
     */


    public function createCustomer(array $data): array
    {
        try {
            $customer = Customer::create($data);

            // $customer->notify(new SendWhatsAppNotification("مرحبا {$customer->name}! لقد تم تسجيلك لدينا بنجاح."));

            return $this->successResponse('تم إنشاء العميل ', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء إنشاء العميل: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء العميل');
        }
    }


    /**
     * Update an existing customer.
     *
     * @param array $data Array containing updated customer details ['name', 'phone', 'notes'].
     * @param Customer $customer The customer to be updated.
     * @return array Response containing status, message, and updated customer data.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            $customer->update($data);

            return $this->successResponse('تم تحديث العميل بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء تحديث العميل: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث العميل');
        }
    }

    /**
     * Delete a customer.
     *
     * @param Customer $customer The customer to be deleted.
     * @return array Response containing status and message.
     */
    public function deleteCustomer(Customer $customer): array
    {
        try {
            $customer->delete();

            return $this->successResponse('تم حذف العميل بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء حذف العميل: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف العميل');
        }
    }

    /**
     * Helper method for success responses.
     *
     * @param string $message Success message.
     * @param int $status HTTP status code (default 200).
     * @param mixed $data Additional data for the response.
     * @return array Response structure for successful operations.
     */
    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    /**
     * Helper method for error responses.
     *
     * @param string $message Error message.
     * @param int $status HTTP status code (default 500).
     * @return array Response structure for failed operations.
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
