<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use App\Services\ProductCategoryService;

use App\Http\Requests\ProductCategoryRequest\fitrtingData;
use App\Http\Requests\ProductCategoryRequest\StoreProductCategory;
use App\Http\Requests\ProductCategoryRequest\StoreProductCategoryData;
use App\Http\Requests\ProductCategoryRequest\UpdateProductCategoryData;

class ProductCategoryController extends Controller
{

    protected ProductCategoryService $productcategoryService;



    public function __construct(ProductCategoryService $productcategoryService)
    {
        $this->productcategoryService = $productcategoryService;
    }

    /**
     * Retrieve and paginate a list of product categories.
     *
     * @return JsonResponse Returns paginated list of product categories or error response
     */
    public function index(fitrtingData $request): JsonResponse
    {
        $result = $this->productcategoryService->getAllProductCategory($request->validated());
        return $result['status'] === 200
             ? $this->successshow($result['data'], $result['message'], $result['status'])
             : $this->error($result['data'], $result['message'], $result['status']);
    }

    /**
     * Store a new product category record in the database.
     *
     * @param StoreProductCategory $request Validated request data containing:
     *    - name (string, required): Name of the product category
     *    - description (string, optional): Description of the product category
     * @return JsonResponse Returns JSON response with operation result
     */
    public function store(StoreProductCategoryData $request): JsonResponse
    {
        $result = $this->productcategoryService->createProductCategory($request->validated());

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update an existing product category record.
     *
     * @param UpdateProductCategoryData $request Validated request data containing fields to update:
     *    - name (string, optional): Updated name of the product category
     *    - description (string, optional): Updated description for the product category
     * @param ProductCategory $productCategory The product category model instance to be updated
     * @return JsonResponse Returns JSON response with operation result
     */
    public function update(UpdateProductCategoryData $request, ProductCategory $productCategory): JsonResponse
    {
        $result = $this->productcategoryService->updateProductCategory(
            $request->validated(),
            $productCategory
        );

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Delete a product category record from the database.
     *
     * @param ProductCategory $productCategory The product category model instance to be deleted
     * @return JsonResponse Returns JSON response with operation result
     */
    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        $result = $this->productcategoryService->deleteProductCategory($productCategory);

        return $result['status'] === 200
             ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
