<?php
namespace App\Services;

use Exception;
use App\Models\ProductOrigin;
use Illuminate\Support\Facades\Log;

class ProductOriginService
{
    public function getAllProductOrigin()
    {
        try {
            $origins = ProductOrigin::select('id', 'name')->get();
            return $this->successResponse('تم استرجاع الصنف  بنجاح', 200, $origins);
        } catch (Exception $e) {
            Log::error('خطأ أثناء استرجاع الصنف : ' . $e->getMessage());
            return $this->errorResponse('فشل في استرجاع الصتف المنتجات');
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
