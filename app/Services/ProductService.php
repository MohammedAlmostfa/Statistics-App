<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    /**
     * Get all products with pagination.
     *
     * @return array Response containing status, message, and data.
     */
    public function getAllProducts($filteringData)
    {
        try {

            $products = Product::select('id', 'name', "selling_price", 'Dollar_exchange', 'dolar_buying_price', 'quantity', 'installment_price', 'created_at', 'origin_id', 'user_id', 'category_id')

                ->with([
                    'origin:id,name',
                    'category:id,name',
                    'user:id,name',
                ])->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                })
                ->paginate(10);


            return [
                'status' => 200,
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data' =>     $products,

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
     * Create a new product.
     *
     * @param array $data Product data.
     * @return array Response containing status, message, and created product data.
     */
    public function createProduct(array $data)
    {
        try {
            $userId = Auth::id();

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
            $product->load(['category', 'user', 'origin']);

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
     * Update an existing product.
     *
     * @param array $data Updated product data.
     * @param Product $product Product model instance.
     * @return array Response containing status and message.
     */
    public function updateProduct(array $data, Product $product)
    {
        try {
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

            return [
                'status' => 200,
                'message' => 'تم تحديث المنتج بنجاح.',
                'data' => $product,
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
     * Delete a product.
     *
     * @param Product $product Product model instance.
     * @return array Response containing status and message.
     */
    public function deleteProduct(Product $product)
    {
        try {
            $product->delete();

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
