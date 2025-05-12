<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Service class for managing products.
 * Includes methods for retrieving, creating, updating, and deleting products.
 */
class ProductService
{
    /**
     * Retrieve all products, optionally filtered by provided criteria.
     *
     * This method will fetch the products, either with or without filtering, and
     * return them in a paginated format. The results are cached for performance optimization.
     *
     * @param array|null $filteringData Optional filtering criteria.
     * @return array Structured response with success or error message in Arabic.
     */
    public function getAllProducts($filteringData = null): array
    {
        try {

            $page = request('page', 1);

            $cacheKey = 'products' . $page . (!empty($filteringData) ? '_' . md5(json_encode($filteringData)) : '');

            $cacheKeys = Cache::get('all_products_keys', []);

            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('all_products_keys', $cacheKeys, now()->addHours(120));
            }

            // Use cache if available
            $products = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($filteringData) {
                return Product::select(
                    'id',
                    'name',
                    'selling_price',
                    'dolar_buying_price',
                    'dollar_exchange',
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
                        $query->filterBy($filteringData);
                    })
                    ->orderByDesc('created_at')
                    ->paginate(10);
            });

            return [
                'status'  => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data'    => $products,
            ];
        } catch (Exception $e) {
            Log::error('Error in getAllProducts: ' . $e->getMessage());
            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء جلب المنتجات، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Create a new product record in the database.
     *
     * This method will create a new product and log the activity.
     *
     * @param array $data Product data to create the new product.
     * @return array Structured response with success or error message in Arabic.
     */
    public function createProduct(array $data): array
    {
        DB::beginTransaction();

        try {
            // Get the authenticated user's ID
            $userId = Auth::id();

            // Create the new product in the database
            $product = Product::create([
                'name'               => $data['name'],
                'dollar_exchange'    => $data['dollar_exchange'],
                'selling_price'      => $data['selling_price'],
                'installment_price'  => $data['installment_price'],
                'origin_id'          => $data['origin_id'],
                'category_id'        => $data['category_id'],
                'quantity'           => $data['quantity'],
                'dolar_buying_price' => $data['dolar_buying_price'],
                'user_id'            => $userId,
            ]);

            // Log the activity of adding a new product
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة المنتج: ' . $product->name,
                'type_id'     => $product->id,
                'type_type'   => Product::class,
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return [
                'status'  => 201,
                'message' => 'تم إنشاء المنتج بنجاح.',
                'data'    => $product,
            ];
        } catch (Exception $e) {
            // Log the error and rollback the transaction
            Log::error('Error in createProduct: ' . $e->getMessage());
            DB::rollBack();

            // Return failure response
            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء إنشاء المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing product in the database.
     *
     * This method will update the product data with the provided values and log the activity.
     *
     * @param array $data Updated product data.
     * @param int $id ID of the product to update.
     * @return array Structured response with success or error message in Arabic.
     */
    public function updateProduct(array $data, $id): array
    {
        DB::beginTransaction();

        try {
            // Lock the product record to prevent concurrent modifications
            $updatedProduct = Product::lockForUpdate()->findOrFail($id);
            $userId = Auth::id();

            // Update the product with the new data
            $updatedProduct->update([
                'name'               => $data['name'] ?? $updatedProduct->name,
                'dollar_exchange'    => $data['dollar_exchange'] ?? $updatedProduct->dollar_exchange,
                'selling_price'      => $data['selling_price'] ?? $updatedProduct->selling_price,
                'installment_price'  => $data['installment_price'] ?? $updatedProduct->installment_price,
                'origin_id'          => $data['origin_id'] ?? $updatedProduct->origin_id,
                'category_id'        => $data['category_id'] ?? $updatedProduct->category_id,
                'quantity'           => $data['quantity'] ?? $updatedProduct->quantity,
                'dolar_buying_price' => $data['dolar_buying_price'] ?? $updatedProduct->dolar_buying_price,
            ]);

            // Log the activity based on the change (whether quantity changed or not)
            if ($data['quantity']) {
                ActivitiesLog::create([
                    'user_id'     => $userId,
                    'description' => 'تم تعديل كمية المنتج: ' . $updatedProduct->name,
                    'type_id'     => $updatedProduct->id,
                    'type_type'   => Product::class,
                ]);
            } else {
                ActivitiesLog::create([
                    'user_id'     => $userId,
                    'description' => 'تم تعديل المنتج: ' . $updatedProduct->name,
                    'type_id'     => $updatedProduct->id,
                    'type_type'   => Product::class,
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Return success response
            return [
                'status'  => 200,
                'message' => 'تم تحديث المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            // Log the error and rollback the transaction
            Log::error('Error in updateProduct: ' . $e->getMessage());
            DB::rollBack();

            // Return failure response
            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء تحديث المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete a product from the database.
     *
     * This method will delete the specified product and log the activity.
     *
     * @param Product $product The product to delete.
     * @return array Structured response with success or error message in Arabic.
     */
    public function deleteProduct(Product $product): array
    {
        DB::beginTransaction();

        try {
            // Delete the product from the database
            $product->delete();
            $userId = Auth::id();

            // Log the activity of deleting the product
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف المنتج: ' . $product->name,
                'type_id'     => $product->id,
                'type_type'   => Product::class,
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return [
                'status'  => 200,
                'message' => 'تم حذف المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            // Log the error and rollback the transaction
            Log::error('Error in deleteProduct: ' . $e->getMessage());
            DB::rollBack();

            // Return failure response
            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء حذف المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
