<?php

namespace App\Services;

use App\Models\ActivitiesLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class ActivitiesLogService
{
    public function getAllActivitiesLog()
    {
        try {
            $activitiesLog = ActivitiesLog::with('user')->paginate(10);

            return $this->successResponse('تم جلب سجلات الأنشطة بنجاح.', 200, $activitiesLog);
        } catch (QueryException $e) {
            Log::error('Database query error: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب سجلات الأنشطة.');
        } catch (Exception $e) {
            Log::error('General error retrieving activity logs: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء جلب سجلات الأنشطة.');
        }
    }

    private function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status'  => $status,
            'data'    => $data,
        ];
    }

    private function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status'  => $status,
        ];
    }
}
