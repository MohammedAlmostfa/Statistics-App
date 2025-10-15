<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImportResource;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    /**
     * The ImportService instance.
     *
     * @var ImportService
     */
    protected ImportService $importService;

    /**
     * Constructor
     *
     * Inject the ImportService to handle business logic related to imports.
     *
     * @param ImportService $importService
     */
    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Display a listing of imports with pagination.
     *
     * This method handles the request for fetching all types of imports (cash, installments,
     * installment payments, and debt payments). It delegates the data fetching and processing
     * to the ImportService, then formats the response using ImportResource.
     *
     * @param Request $request
     * @return JsonResponse
     */
 public function index(Request $request): JsonResponse
{

    $data = [
        'date' => $request->input('date')
    ];

    $result = $this->importService->getAllImports($data);

    return $result['status'] === 200
        ? $this->success($result['data'], $result['message'], $result['status'])
        : $this->error(null, $result['message'], $result['status']);
}


}
