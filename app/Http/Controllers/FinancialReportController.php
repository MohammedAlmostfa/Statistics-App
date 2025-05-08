<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterFinancialReportData;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{ /**
     * @var CustomerService $customerService Handles customer business logic
     *
     * @documented
     */
    protected FinancialReportService $FinancialReportService;

    /**
     * CustomerController Constructor
     * Initializes the CustomerService dependency for handling customer-related logic.
     *
     * @param CustomerService $customerService Dependency injected service for customer operations
     *
     * @documented
     */
    public function __construct(FinancialReportService $FinancialReportService)
    {
        $this->FinancialReportService = $FinancialReportService;
    }
    public function index(FilterFinancialReportData $request)
    {
        $validatedData=$request->validated();
        $result= $this->FinancialReportService->GetFinancialReport($validatedData);

        return $result['status'] === 200
                  ? $this->success($result['data'], $result['message'], $result['status'])
                  : $this->error(null, $result['message'], $result['status']);
    }
}
