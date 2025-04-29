<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CustomerRequest\fitrtingData;
use App\Http\Requests\DebetRequest\fitrtinDebetgData;
use App\Http\Requests\CustomerRequest\StoreCustomerData;
use App\Http\Requests\CustomerRequest\UpdateCustomerData;

/**
 * CustomerController manages customer-related operations, such as:
 * - Retrieving a list of customers
 * - Showing customer details (including debts)
 * - Creating, updating, and deleting customer records
 */
class CustomerController extends Controller
{
    /**
     * @var CustomerService $customerService Handles customer business logic
     */
    protected CustomerService $customerService;

    /**
     * CustomerController Constructor
     * Initializes the CustomerService dependency for handling customer-related logic.
     *
     * @param CustomerService $customerService Dependency injected service for customer operations
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Retrieve and paginate a list of customers.
     *
     * @return JsonResponse Returns paginated list of customers or error response
     */
    public function index(fitrtingData $request): JsonResponse
    {
        $result = $this->customerService->getAllCustomers($request->validated());
        return $result['status'] === 200
             ? $this->success($result['data'], $result['message'], $result['status'])
             : $this->error($result['data'], $result['message'], $result['status']);
    }



    /**
     * Store a new customer record in the database.
     *
     * @param StoreCustomerData $request Validated request data containing:
     *    - name (string, required): Name of the customer
     *    - phone (string, required): Phone number of the customer
     *    - notes (string, optional): Additional notes about the customer
     * @return JsonResponse Returns JSON response with operation result
     */
    public function store(StoreCustomerData $request): JsonResponse
    {
        $result = $this->customerService->createCustomer($request->validated());

        return $result['status'] === 200
   ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update an existing customer record.
     *
     * @param UpdateCustomerData $request Validated request data containing fields to update:
     *    - name (string, optional): Updated name of the customer
     *    - phone (string, optional): Updated phone number of the customer
     *    - notes (string, optional): Updated notes for the customer
     * @param Customer $customer The customer model instance to be updated
     * @return JsonResponse Returns JSON response with operation result
     */
    public function update(UpdateCustomerData $request, Customer $customer): JsonResponse
    {
        $result = $this->customerService->updateCustomer(
            $request->validated(),
            $customer
        );

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Delete a customer record from the database.
     *
     * @param Customer $customer The customer model instance to be deleted
     * @return JsonResponse Returns JSON response with operation result
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $result = $this->customerService->deleteCustomer($customer);

        return $result['status'] === 200
             ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
