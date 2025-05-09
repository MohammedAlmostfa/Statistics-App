<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * Retrieve all products, optionally filtered by provided criteria.
     *
     * @param array|null $filteringData
     * @return array
     */
    public function getAllProducts($filteringData = null): array
    {
        try {
            $page = request('page', 1);
            $cacheKey = 'products' . $page . (!empty($filteringData) ? '_' . md5(json_encode($filteringData)) : '');

            $products = Cache::remember($cacheKey, 1000, function () use ($filteringData) {
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
     * @param array $data
     * @return array
     */
    public function createProduct(array $data): array
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

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

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة المنتج: ' . $product->name,
                'type_id'     => $product->id,
                'type_type'   => Product::class,
            ]);

            DB::commit();

            return [
                'status'  => 201,
                'message' => 'تم إنشاء المنتج بنجاح.',
                'data'    => $product,
            ];
        } catch (Exception $e) {
            Log::error('Error in createProduct: ' . $e->getMessage());
            DB::rollBack();

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء إنشاء المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Update an existing product in the database.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    public function updateProduct(array $data, $id): array
    {
        DB::beginTransaction();

        try {
            $updatedProduct = Product::lockForUpdate()->findOrFail($id);
            $userId = Auth::id();

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

            DB::commit();

            return [
                'status'  => 200,
                'message' => 'تم تحديث المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error in updateProduct: ' . $e->getMessage());
            DB::rollBack();

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء تحديث المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }

    /**
     * Delete a product from the database.
     *
     * @param Product $product
     * @return array
     */
    public function deleteProduct(Product $product): array
    {
        DB::beginTransaction();

        try {
            $product->delete();
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف المنتج: ' . $product->name,
                'type_id'     => $product->id,
                'type_type'   => Product::class,
            ]);

            DB::commit();

            return [
                'status'  => 200,
                'message' => 'تم حذف المنتج بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error in deleteProduct: ' . $e->getMessage());
            DB::rollBack();

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء حذف المنتج، يرجى المحاولة مرة أخرى.',
            ];
        }
    }
}
