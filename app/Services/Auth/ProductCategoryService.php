<?php
namespace App\Services;

use Exception;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Log;

class ProductCategoryService
{
    /**
     * Retrieve all product categories with optional filtering.
     *
     * @param array|null $filteringData
     * @return array
     */
    public function getAllProductCategory(array $filteringData = null): array
    {
        try {
            $categories = ProductCategory::query()
                ->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                })
                ->get();

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
        try {
            $category = ProductCategory::create($data);
            return $this->successResponse('تم إنشاء الصنف بنجاح', 201, $category);
        } catch (Exception $e) {
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
        try {
            $productcategory->update($data);
            return $this->successResponse('تم تحديث الصنف بنجاح', 200, $productcategory);
        } catch (Exception $e) {
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
        try {
            $productcategory->delete();
            return $this->successResponse('تم حذف الصنف بنجاح', 200);
        } catch (Exception $e) {
            Log::error('خطأ أثناء حذف الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في حذف الصنف');
        }
    }

    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
