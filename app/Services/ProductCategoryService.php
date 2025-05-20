<?php

namespace App\Services;

use App\Models\ActivitiesLog;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service class to handle product categories.
 * Includes methods for retrieving, creating, updating, and deleting product categories.
 */
class ProductCategoryService extends Service
{
    /**
     * Retrieve all product categories from cache or database.
     *
     * This method will first check the cache for existing categories and return them.
     * If not found, it will fetch the categories from the database and cache them for later use.
     *
     * @return array Structured response with a success or error message in Arabic.
     */
    public function getAllProductCategory(): array
    {
        try {
            $cacheKey = 'categories';  // Cache key to store the product categories

            // Retrieve categories from the cache or fetch them from the database if not cached
            $categories = Cache::remember($cacheKey, now()->addMinutes(360), function () {
                return ProductCategory::select('id', 'name')->get();
            });

            // Return success response with categories data
            return $this->successResponse('تم استرجاع الأصناف بنجاح', 200, $categories);
        } catch (Exception $e) {
            // Log the error and return a failure response
            Log::error('خطأ أثناء استرجاع الأصناف: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ اثناء استرجاع اصناف المنتجات ,  يرجى المحاولة مرة اخرى');
        }
    }

    /**
     * Create a new product category.
     *
     * This method will create a new product category record and log the activity.
     *
     * @param array $data Product category data including name and other attributes.
     * @return array Structured response with success or error message in Arabic.
     */
    public function createProductCategory(array $data): array
    {
        DB::beginTransaction();

        try {
            // Create the new product category in the database
            $productcategory = ProductCategory::create($data);
            $userId = Auth::id();  // Get the authenticated user's ID

            // Log the activity of adding a new product category
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            // Commit the transaction
            DB::commit();

            // Return a success response
            return $this->successResponse('تم إنشاء الصنف بنجاح', 200);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            Log::error('خطأ أثناء إنشاء الصنف: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ اثناء انشاء صنف المنتج ,  يرجى المحاولة مرة اخرى');
        }
    }

    /**
     * Update an existing product category.
     *
     * This method will update the details of an existing product category and log the activity.
     *
     * @param array $data Updated product category data.
     * @param ProductCategory $productcategory The product category to update.
     * @return array Structured response with success or error message in Arabic.
     */
    public function updateProductCategory(array $data, ProductCategory $productcategory): array
    {
        DB::beginTransaction();

        try {
            // Update the product category with the new data
            $productcategory->update($data);
            $userId = Auth::id();  // Get the authenticated user's ID

            // Log the activity of updating the product category
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            // Commit the transaction
            DB::commit();

            // Return a success response
            return $this->successResponse('تم تحديث الصنف بنجاح', 200);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            Log::error('خطأ أثناء تحديث الصنف: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ اثناء تحديث صنف المنتج , يرجى المحاولة مرة اخرى');
        }
    }

    /**
     * Delete a product category.
     *
     * This method will delete a product category and log the activity.
     *
     * @param ProductCategory $productcategory The product category to delete.
     * @return array Structured response with success or error message in Arabic.
     */
    public function deleteProductCategory(ProductCategory $productcategory): array
    {
        DB::beginTransaction();

        try {
            // Delete the product category from the database
            $productcategory->delete();
            $userId = Auth::id();  // Get the authenticated user's ID

            // Log the activity of deleting the product category
            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            // Commit the transaction
            DB::commit();

            // Return a success response
            return $this->successResponse('تم حذف الصنف بنجاح', 200);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            Log::error('خطأ أثناء حذف الصنف: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ اثناء حذف صنف المنتج , يرجى المحاولة مرة اخرى');
        }
    }


}
