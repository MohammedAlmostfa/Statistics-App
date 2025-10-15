<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    protected ExportService $exportService;

    /**
     * Constructor
     *
     * Inject the ImportService to handle business logic related to imports.
     *
     * @param exportService $importService
     */
    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }


    public function index(Request $request): JsonResponse
    {
        $date = $request->input('date'); // يمكن أن يكون null

        $result = $this->exportService->getAllExports($date);

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
