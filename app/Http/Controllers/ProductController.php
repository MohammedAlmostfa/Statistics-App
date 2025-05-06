<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductRequest\filtterdata;
use App\Http\Requests\ProductRequest\StoreProductData;
use App\Http\Requests\ProductRequest\UpdateProductData;

class ProductController extends Controller
{
    /**
     * The product service instance.
     *
     * @var ProductService
     */
    protected $productService;

    /**
     * Create a new ProductController instance.
     *
     * @param ProductService $productService The service responsible for handling business logic.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Retrieve all products with optional filters.
     *
     * @param filtterdata $request Validated filter data for retrieving products.
     * @return \Illuminate\Http\JsonResponse JSON response with paginated product data.
     */
    public function index(filtterdata $request)
    {
        // Fetch products using ProductService with applied filters.
        $result = $this->productService->getAllProducts($request->validated());

        // Return paginated response if successful, otherwise return error.
        return $result['status'] === 200
            ? $this->success(ProductResource::collection($result['data']), $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

    /**
     * Store a new product in the database.
     *
     * @param StoreProductData $request Validated product data.
     * @return \Illuminate\Http\JsonResponse JSON response confirming creation success or failure.
     */
    public function store(StoreProductData $request)
    {
        // Validate and extract data from request.
        $validatedData = $request->validated();

        // Create the product using ProductService.
        $result = $this->productService->createProduct($validatedData);

        // Return success response if product was created, otherwise return error.
        return $result['status'] === 201
            ? $this->success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

    /**
     * Update an existing product's data.
     *
     * @param UpdateProductData $request Validated update data.
     * @param Product $product The product instance to be updated.
     * @return \Illuminate\Http\JsonResponse JSON response confirming update success or failure.
     */
    public function update(UpdateProductData $request, $id)
    {
        // Validate and extract data from request.
        $validatedData = $request->validated();

        // Update the product using ProductService.
        $result = $this->productService->updateProduct($validatedData, $id);

        // Return success response if update was successful, otherwise return error.
        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

    /**
     * Delete a product from the database.
     *
     * @param Product $product The product instance to be deleted.
     * @return \Illuminate\Http\JsonResponse JSON response confirming deletion success or failure.
     */
    public function destroy(Product $product)
    {
        // Delete the product using ProductService.
        $result = $this->productService->deleteProduct($product);

        // Return success response if deletion was successful, otherwise return error.
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
}
