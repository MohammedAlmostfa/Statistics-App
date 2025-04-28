<?php

namespace App\Services;

use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Handles customer CRUD (Create, Read, Update, Delete) operations.
 * Provides methods for managing customer data and interacting with the database.
 */
class CustomerService
{
    /**
     * Retrieve all customers.
     *
     * Fetches all customers from the database.
     *
     * @return array Response containing status, message, and list of customers.
     */
    public function getAllCustomers($filteringData)
    {
        try {

            $customers = Customer::query()
                ->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                })
                ->get();


            return $this->successResponse($customers, 'تم استرجاع العملاء بنجاح', 200);
        } catch (Exception $e) {

            Log::error('خطأ أثناء استرجاع العملاء: ' . $e->getMessage());


            return $this->errorResponse('فشل في استرجاع العملاء');
        }
    }


    /**
     * Retrieve customer debts.
     *
     * Fetches the customer and their associated debts using eager loading.
     *
     * @param int $id ID of the customer.
     * @return array Response containing status, message, and customer debts.
     */
    public function getCustomerDebts($id, $filteringData)
    {
        try {
            // Fetch the customer along with their debts
            $customer = Customer::with(['debts' => function ($query) use ($filteringData) {
                $query->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                });
            }])->findOrFail($id);

            // Return success response with debts
            return $this->successResponse($customer->debts, 'تم استرجاع ديون العميل بنجاح', 200);
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء استرجاع ديون العميل: ' . $e->getMessage());

            // Return error response
            return $this->errorResponse('فشل في استرجاع ديون العميل');
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
            // Create new customer record
            $customer = Customer::create($data);

            // Return success response
            return $this->successResponse($customer, 'تم إنشاء العميل بنجاح', 200);
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء إنشاء العميل: ' . $e->getMessage());

            // Return error response
            return $this->errorResponse('فشل في إنشاء العميل');
        }
    }

    /**
     * Update an existing customer.
     *
     * Updates the details of an existing customer.
     *
     * @param array $data Array containing updated customer details ['name', 'phone', 'notes'].
     * @param Customer $customer The customer to be updated.
     * @return array Response containing status, message, and updated customer data.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            // Update customer record
            $customer->update($data);

            // Return success response
            return $this->successResponse($customer, 'تم تحديث العميل بنجاح');
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء تحديث العميل: ' . $e->getMessage());

            // Return error response
            return $this->errorResponse('فشل في تحديث العميل');
        }
    }

    /**
     * Delete a customer.
     *
     * Removes a customer from the database.
     *
     * @param Customer $customer The customer to be deleted.
     * @return array Response containing status and message.
     */
    public function deleteCustomer(Customer $customer): array
    {
        try {
            // Delete customer record
            $customer->delete();

            // Return success response
            return $this->successResponse(null, 'تم حذف العميل بنجاح');
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء حذف العميل: ' . $e->getMessage());

            // Return error response
            return $this->errorResponse('فشل في حذف العميل');
        }
    }

    /**
     * Helper method for success responses.
     *
     * @param mixed $data Data to be included in the response.
     * @param string $message Success message.
     * @param int $status HTTP status code (default 200).
     * @return array Response structure for successful operations.
     */
    private function successResponse($data, string $message, int $status = 200): array
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
            'data' => null,
        ];
    }
}
