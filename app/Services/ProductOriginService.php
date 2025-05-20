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
class ProductOriginService extends Service
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
            return $this->errorResponse("حدث خطأ اثناء استرجاع اصماف المنتجات , يرجى المحاولة مرة اخرى");
        }
    }

}
