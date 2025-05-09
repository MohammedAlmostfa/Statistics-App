<?php

namespace App\Services;

use App\Models\ActivitiesLog;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductCategoryService
{
    /**
     * Retrieve all product categories from cache or database.
     *
     * @return array
     */
    public function getAllProductCategory(): array
    {
        try {
            $cacheKey = 'categories';

            $categories = Cache::remember($cacheKey, 1000, function () {
                return ProductCategory::select('id', 'name')->get();
            });

            return $this->successResponse('تم استرجاع الأصناف بنجاح', 200, $categories);
        } catch (Exception $e) {
            Log::error('خطأ أثناء استرجاع الأصناف: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الأصناف');
        }
    }

    /**
     * Create a new product category.
     *
     * @param array $data
     * @return array
     */
    public function createProductCategory(array $data): array
    {
        DB::beginTransaction();

        try {
            $productcategory = ProductCategory::create($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم إضافة فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            DB::commit();

            return $this->successResponse('تم إنشاء الصنف بنجاح', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إنشاء الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في إنشاء الصنف');
        }
    }

    /**
     * Update an existing product category.
     *
     * @param array $data
     * @param ProductCategory $productcategory
     * @return array
     */
    public function updateProductCategory(array $data, ProductCategory $productcategory): array
    {
        DB::beginTransaction();

        try {
            $productcategory->update($data);
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم تعديل فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            DB::commit();

            return $this->successResponse('تم تحديث الصنف بنجاح', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تحديث الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في تحديث الصنف');
        }
    }

    /**
     * Delete a product category.
     *
     * @param ProductCategory $productcategory
     * @return array
     */
    public function deleteProductCategory(ProductCategory $productcategory): array
    {
        DB::beginTransaction();

        try {
            $productcategory->delete();
            $userId = Auth::id();

            ActivitiesLog::create([
                'user_id'     => $userId,
                'description' => 'تم حذف فئة المنتج: ' . $productcategory->name,
                'type_id'     => $productcategory->id,
                'type_type'   => ProductCategory::class,
            ]);

            DB::commit();

            return $this->successResponse('تم حذف الصنف بنجاح', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حذف الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الصنف');
        }
    }

    /**
     * Build a standard success response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $data
     * @return array
     */
    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status'  => $status,
            'data'    => $data,
        ];
    }

    /**
     * Build a standard error response.
     *
     * @param string $message
     * @param int $status
     * @return array
     */
    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status'  => $status,
        ];
    }
}
