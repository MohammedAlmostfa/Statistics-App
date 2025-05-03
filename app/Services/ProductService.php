<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Class ProductService
 *
 * This service handles operations related to the Product model,
 * including retrieval, creation, updating, and deletion of products.
 */
class ProductService
{
    /**
     * Retrieve all products, optionally filtered by provided criteria.
     *
     * @param array|null $filteringData Optional filtering data.
     * @return array An array containing status, message, and the list of products.
     */
    public function getAllProducts($filteringData = null): array
    {
        try {
            // Generate a unique cache key based on filters if any
            $cacheKey = 'products' . (!empty($filteringData) ? '_' . md5(json_encode($filteringData)) : '');

            // Retrieve from cache or query the DB
            $products = Cache::remember($cacheKey, 1000, function () use ($filteringData) {
                return Product::select(
                    'id',
                    'name',
                    'selling_price',
                    'Dollar_exchange',
                    'dolar_buying_price',
                    'quantity',
                    'installment_price',
                    'created_at',
                    'origin_id',
                    'user_id',
                    'category_id'
                )
                    ->with([
                        'origin:id,name',
                        'category:id,name',
                        'user:id,name'
                    ])
                    ->when(!empty($filteringData), function ($query) use ($filteringData) {
                        $query->filterBy($filteringData); // custom local scope (needs to be defined in model)
                    })
                    ->get();
            });

            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' => $products,
            ];
        } catch (Exception $e) {
            Log::error('Error in getAllProducts: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Create a new product record in the database.
     *
     * @param array $data The data used to create the product.
     * @return array An array containing status, message, and created product data.
     */
    public function createProduct(array $data): array
    {
        try {
            $userId = Auth::id(); // Get authenticated user ID

            // Create the new product
            $product = Product::create([
                'name' => $data['name'],
                'Dollar_exchange' => $data['Dollar_exchange'],
                'selling_price' => $data['selling_price'],
                'installment_price' => $data['installment_price'],
                'origin_id' => $data['origin_id'],
                'category_id' => $data['category_id'],
                'quantity' => $data['quantity'],
                'dolar_buying_price' => $data['dolar_buying_price'],
                'user_id' => $userId,
            ]);

            // Invalidate cache
            Cache::forget('products');

            return [
                'status' => 201,
                'message' => 'تم إنشاء المنتج بنجاح.',
                'data' => $product,
            ];
        } catch (Exception $e) {
            Log::error('Error in createProduct: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing product in the database.
     *
     * @param array $data The new data to update.
     * @param Product $product The product to update.
     * @return array An array containing status and message.
     */
    public function updateProduct(array $data, Product $product): array
    {
        try {
            // Update fields, fallback to current value if not provided
            $product->update([
                'name' => $data['name'] ?? $product->name,
                'Dollar_exchange' => $data['Dollar_exchange'] ?? $product->Dollar_exchange,
                'selling_price' => $data['selling_price'] ?? $product->selling_price,
                'installment_price' => $data['installment_price'] ?? $product->installment_price,
                'origin_id' => $data['origin_id'] ?? $product->origin_id,
                'category_id' => $data['category_id'] ?? $product->category_id,
                'quantity' => $data['quantity'] ?? $product->quantity,
                'dolar_buying_price' => $data['dolar_buying_price'] ?? $product->dolar_buying_price,
            ]);

            // Clear cache
            Cache::forget('products');

            return [
                'status' => 200,
                'message' => 'تم تحديث المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error in updateProduct: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete a product from the database.
     *
     * @param Product $product The product to delete.
     * @return array An array containing status and message.
     */
    public function deleteProduct(Product $product): array
    {
        try {
            // Delete product
            $product->delete();

            // Clear cache
            Cache::forget('products');

            return [
                'status' => 200,
                'message' => 'تم حذف المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error in deleteProduct: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
