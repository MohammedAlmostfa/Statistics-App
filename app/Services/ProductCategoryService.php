<?php

namespace App\Services;

use Exception;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ProductCategoryService handles all CRUD operations for product categories.
 * It also uses caching to optimize performance and reduce database load.
 */
class ProductCategoryService
{
    /**
     * Retrieve all product categories from cache or database.
     *
     * @return array Response containing status, message, and categories data.
     */
    public function getAllProductCategory(): array
    {
        try {
            $cacheKey = 'categories';

            // Retrieve categories from cache, or fetch from DB and store in cache
            $categories = Cache::remember($cacheKey, 1000, function () {
                return ProductCategory::select('id', 'name')->get();
            });

            return $this->successResponse('تم استرجاع الأصناف بنجاح', 200, $categories);
        } catch (Exception $e) {
            // Log any unexpected error and return a failure response
            Log::error('خطأ أثناء استرجاع الأصناف: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الأصناف');
        }
    }

    /**
     * Create a new product category and clear cached list.
     *
     * @param array $data The data used to create the new category (e.g., ['name' => 'Electronics']).
     * @return array Response with status and newly created category.
     */
    public function createProductCategory(array $data): array
    {
        try {
            // Create the category using Eloquent
            $category = ProductCategory::create($data);


            return $this->successResponse('تم إنشاء الصنف بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء إنشاء الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء الصنف');
        }
    }

    /**
     * Update an existing product category and clear cached list.
     *
     * @param array $data Updated category data (e.g., ['name' => 'Updated Name']).
     * @param ProductCategory $productcategory The category instance to update.
     * @return array Response with updated category info.
     */
    public function updateProductCategory(array $data, ProductCategory $productcategory): array
    {
        try {
            // Update the category using Eloquent
            $productcategory->update($data);



            return $this->successResponse('تم تحديث الصنف بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء تحديث الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث الصنف');
        }
    }

    /**
     * Delete a product category and clear cached list.
     *
     * @param ProductCategory $productcategory The category instance to delete.
     * @return array Response indicating success or failure.
     */
    public function deleteProductCategory(ProductCategory $productcategory): array
    {
        try {


            // Delete the category using Eloquent
            $productcategory->delete();


            return $this->successResponse('تم حذف الصنف بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء حذف الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الصنف');
        }
    }

    /**
     * Build a standard success response.
     *
     * @param string $message A success message.
     * @param int $status HTTP status code.
     * @param mixed $data Optional data to include in the response.
     * @return array
     */
    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    /**
     * Build a standard error response.
     *
     * @param string $message An error message.
     * @param int $status HTTP status code.
     * @return array
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
