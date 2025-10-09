<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

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


 public function index()
{
    $result = $this->exportService->getAllExports();

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
