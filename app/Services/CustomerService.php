<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

/**
 * CustomerService
 *
 * Service class for managing customer records.
 * Provides methods for retrieving, creating, updating, and deleting customers
 * with caching and error logging support.
 */
class CustomerService
{
    /**
     * Retrieve all customers with optional filtering and pagination.
     * Results are cached for improved performance.
     *
     * @param array|null $filteringData Optional filters (e.g., name, phone).
     * @return array Structured success or error response.
     */
    public function getAllCustomers($filteringData): array
    {
        try {
            $page = request('page', 1);

            // Generate a unique cache key based on page and filters
            $cacheKey = 'customers_' . $page . (empty($filteringData) ? '' : md5(json_encode($filteringData)));

            // Retrieve customers from cache or query database
            $customers = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($filteringData) {
                return Customer::query()
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->paginate(10);
            });

            return $this->successResponse('Customers retrieved successfully.', 200, $customers);

        } catch (QueryException $e) {
            // Log query-specific errors
            Log::error('Database query error while fetching customers: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve customers.');
        } catch (Exception $e) {
            // Log general errors
            Log::error('General error retrieving customers: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve customers.');
        }
    }

    /**
     * Create a new customer record.
     *
     * @param array $data Associative array containing customer details.
     * @return array Structured success or error response.
     */
    public function createCustomer(array $data): array
    {
        try {
            // Create the customer record
            $customer = Customer::create($data);

            // Optionally clear or refresh related cache here if needed

            return $this->successResponse('Customer created successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return $this->errorResponse('Failed to create customer.');
        }
    }

    /**
     * Update an existing customer's information.
     *
     * @param array $data Associative array of updated fields.
     * @param Customer $customer Customer model instance to update.
     * @return array Structured success or error response.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            // Update customer data
            $customer->update($data);

            return $this->successResponse('Customer updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return $this->errorResponse('Failed to update customer.');
        }
    }

    /**
     * Delete a customer record from the database.
     *
     * @param Customer $customer Customer model instance to delete.
     * @return array Structured success or error response.
     */
    public function deleteCustomer(Customer $customer): array
    {
        try {
            // Delete the customer
            $customer->delete();

            return $this->successResponse('Customer deleted successfully.', 200);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete customer.');
        }
    }

    /**
     * Generate a standardized success response.
     *
     * @param string $message Descriptive message.
     * @param int $status HTTP status code (default is 200).
     * @param mixed|null $data Optional data payload.
     * @return array Structured response.
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
     * Generate a standardized error response.
     *
     * @param string $message Descriptive error message.
     * @param int $status HTTP status code (default is 500).
     * @return array Structured response.
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
