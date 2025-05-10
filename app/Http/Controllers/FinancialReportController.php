<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterFinancialReportData;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;

/**
 * Controller for handling financial report-related operations.
 *
 * This controller receives HTTP requests for generating financial reports,
 * delegates business logic to the FinancialReportService, and returns standardized API responses.
 */
class FinancialReportController extends Controller
{
    /**
     * The service responsible for financial report business logic.
     *
     * @var FinancialReportService
     */
    protected FinancialReportService $FinancialReportService;

    /**
     * FinancialReportController Constructor.
     *
     * Injects the FinancialReportService to handle financial report generation and data aggregation.
     *
     * @param FinancialReportService $FinancialReportService
     */
    public function __construct(FinancialReportService $FinancialReportService)
    {
        $this->FinancialReportService = $FinancialReportService;
    }

    /**
     * Generate and return financial report based on provided filters.
     *
     * This method validates the request using FilterFinancialReportData,
     * passes the validated data to the FinancialReportService, and returns a formatted API response.
     *
     * @param FilterFinancialReportData $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(FilterFinancialReportData $request)
    {
        // Validate request input
        $validatedData = $request->validated();

        // Retrieve the report from the service
        $result = $this->FinancialReportService->GetFinancialReport($validatedData);

        // Return appropriate API response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
