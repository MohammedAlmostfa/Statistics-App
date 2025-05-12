<?php

namespace App\Services;

use Exception;
use App\Models\ProductOrigin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ProductOriginService handles retrieval of product origins (e.g., country of origin).
 * It uses caching to reduce database queries and improve performance.
 */
class ProductOriginService
{
    /**
     * Retrieve all product origins from cache or database.
     *
     * @return array Response with status, message, and list of product origins.
     */
    public function getAllProductOrigin(): array
    {
        try {
            $cacheKey = 'origins';

            // Retrieve origins from cache, or fetch from DB and store in cache
            $origins = Cache::remember($cacheKey, now()->addMinutes(1200), function () {
                return ProductOrigin::select('id', 'name')->get();
            });

            return $this->successResponse('تم استرجاع الصنف بنجاح', 200, $origins);
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('خطأ أثناء استرجاع الصنف: ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع أصناف المنتجات');
        }
    }

    /**
     * Create a standard success response.
     *
     * @param string $message Success message.
     * @param int $status HTTP status code (default 200).
     * @param mixed|null $data Optional data to include.
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
     * Create a standard error response.
     *
     * @param string $message Error message.
     * @param int $status HTTP status code (default 500).
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
