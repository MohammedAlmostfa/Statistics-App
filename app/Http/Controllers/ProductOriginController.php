<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ProductOriginService;

class ProductOriginController extends Controller
{
    protected ProductOriginService $productOriginService;

    public function __construct(ProductOriginService $productOriginService)
    {
        $this->productOriginService = $productOriginService;
    }

    /**
     * Retrieve a list of product origins.
     *
     * @return JsonResponse Returns a list of product origins or an error response.
     */
    public function index(): JsonResponse
    {
        $result = $this->productOriginService->getAllProductOrigin();
        return $result['status'] === 200
             ? $this->success($result['data'], $result['message'], $result['status'])
             : $this->error($result['data'] ?? null, $result['message'], $result['status']);
    }
}
