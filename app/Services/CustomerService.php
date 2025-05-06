<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

/**
 * Service class for managing customer data.
 * Provides methods to handle CRUD operations with caching and error handling.
 */
class CustomerService
{
    /**
     * Retrieve all customers, with optional filtering and caching.
     *
     * @param array|null $filteringData Optional filters (e.g., name, phone).
     * @return array Structured response with customer data or error.
     */
    public function getAllCustomers(array $filteringData = null): array
    {
        try {
            $cacheKey = 'customers' . (empty($filteringData) ? '' : md5(json_encode($filteringData)));

            $customers = Cache::remember($cacheKey, 1000, function () use ($filteringData) {
                return Customer::query()
                    ->when(!empty($filteringData), function ($query) use ($filteringData) {
                        $query->filterBy($filteringData);
                    })
                    ->get();
            });

            return $this->successResponse('تم جلب العملاء بنجاح.', 200, $customers);
        } catch (QueryException $e) {
            Log::error('Database query error: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب العملاء.');
        } catch (Exception $e) {
            Log::error('General error retrieving customers: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب العملاء.');
        }
    }

    /**
     * Create a new customer and clear cache.
     *
     * @param array $data Customer details ['name', 'phone', 'notes'].
     * @return array Structured response with success or error.
     */
    public function createCustomer(array $data): array
    {
        try {
            Customer::create($data);

            return $this->successResponse('تم إنشاء العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء العميل.');
        }
    }

    /**
     * Update an existing customer's details and clear cache.
     *
     * @param array $data Updated customer data.
     * @param Customer $customer The customer model instance to update.
     * @return array Structured response with success or error.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            $customer->update($data);


            return $this->successResponse('تم تحديث بيانات العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث بيانات العميل.');
        }
    }

    /**
     * Delete a customer from the database and clear cache.
     *
     * @param Customer $customer The customer model instance to delete.
     * @return array Structured response with success or error.
     */
    public function deleteCustomer(Customer $customer): array
    {
        try {
            $customer->delete();
            return $this->successResponse('تم حذف العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف العميل.');
        }
    }

    /**
     * Format a success response.
     *
     * @param string $message Descriptive success message.
     * @param int $status HTTP status code (default: 200).
     * @param mixed|null $data Optional data payload.
     * @return array
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
     * Format an error response.
     *
     * @param string $message Descriptive error message.
     * @param int $status HTTP status code (default: 500).
     * @return array
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
