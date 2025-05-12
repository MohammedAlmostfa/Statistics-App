<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

/**use Illuminate\Support\Facades\Auth;

 * CustomerService
 *
 * This service provides methods for managing customer records,
 * including retrieving, creating, updating, and deleting customers.
 * It also supports caching and error logging for optimized performance.
 */
class CustomerService
{
    /**
     * Retrieve all customers with optional filtering and caching.
     *
     * @param array|null $filteringData Optional filters (e.g., name, phone).
     * @return array Structured success or error response.
     */
    public function getAllCustomers($filteringData): array
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'customers_' . $page . (empty($filteringData) ? '' : md5(json_encode($filteringData)));
            $cacheKeys = Cache::get('all_customers_keys', []);

            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_customers_keys', $cacheKeys, now()->addHours(2));
            }


            // Retrieve customers from cache or fetch from the database
            $customers = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($filteringData) {
                return Customer::query()
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->orderByDesc('created_at')
                    ->paginate(10);
            });

            return $this->successResponse('تم جلب بيانات العملاء بنجاح.', 200, $customers);
        } catch (QueryException $e) {
            Log::error('Database query error while retrieving customers: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب بيانات العملاء.');
        } catch (Exception $e) {
            Log::error('General error while retrieving customers: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء جلب بيانات العملاء.');
        }
    }

    /**
     * Create a new customer record.
     *
     * @param array $data Customer details.
     * @return array Structured success or error response.
     */
    public function createCustomer(array $data): array
    {
        try {


            // Create the customer record
            $customer = Customer::create($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة زبون: ' . $customer->name,
                'type_id'     => $customer->id,
                'type_type'   => Customer::class,
            ]);

            return $this->successResponse('تم إنشاء العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while creating customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء العميل.');
        }
    }

    /**
     * Update an existing customer's information.
     *
     * @param array $data Updated customer details.
     * @param Customer $customer Customer model instance to update.
     * @return array Structured success or error response.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            // Update the customer record
            $customer->update($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل زبون: ' . $customer->name,
                'type_id'     => $customer->id,
                'type_type'   => Customer::class,
            ]);

            return $this->successResponse('تم تحديث بيانات العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while updating customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث بيانات العميل.');
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


            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف زبون: ' . $customer->name,
                'type_id'     => $customer->id,
                'type_type'   => Customer::class,
            ]);
            // Delete the customer recor

            $customer->delete();


            return $this->successResponse('تم حذف العميل بنجاح.', 200);
        } catch (Exception $e) {
            Log::error('Error while deleting customer: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف العميل.');
        }
    }

    /**
     * Generate a standardized success response.
     *
     * @param string $message Success message.
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
     * @param string $message Error message.
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
