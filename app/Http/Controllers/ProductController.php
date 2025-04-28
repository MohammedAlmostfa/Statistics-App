<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest\StoreProductData;
use App\Http\Requests\ProductRequest\UpdateProductData;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{

    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected $productService;

    /**
     * Create a new UserController instance.
     *
     * @param UserService $userService The user service used to handle logic.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        // Create the user using UserService
        $result = $this->productService->getAllUsers();

        // Return response based on the result
        return $result['status'] === 200
            ? self::success($result['data'], $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    /**
     * Store a new user.
     *
     * @param StoreUserData $request Validated user data.
     * @return \Illuminate\Http\JsonResponse JSON response with status and message.
     */
    public function store(StoreProductData $request)
    {
        // Validate and get the input data
        $validatedData = $request->validated(); // Corrected: use `validated` method

        // Create the user using UserService
        $result = $this->productService->createProduct($validatedData);

        // Return response based on the result
        return $result['status'] === 201
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    public function update(UpdateProductData $request, Product $product)
    {
        // Validate and get the input data
        $validatedData = $request->validated(); // Corrected: use `validated` method

        // Create the user using UserService
        $result = $this->productService->updateProduct($validatedData, $product);

        // Return response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    public function destroy(Product $product)
    {


        // Create the user using UserService
        $result = $this->productService->deleteProduct($product);

        // Return response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

}
