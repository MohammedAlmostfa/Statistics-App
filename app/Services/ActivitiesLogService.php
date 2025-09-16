<?php

namespace App\Services;

use App\Models\ActivitiesLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

/**
 * **ActivitiesLogService**
 *
 * This service handles operations related to activity logs, including:
 * - Retrieving logs with optional filtering and pagination.
 * - Implementing caching for optimized performance.
 * - Logging errors for debugging and stability.
 */
class ActivitiesLogService extends Service
{
    /**
     * **Retrieve all activity logs with filtering and caching**
     *
     * This method retrieves logs from the database and caches frequently accessed data.
     *
     * @param array|null $filteringData Optional filters (e.g., user, date).
     * @return array JSON response containing activity logs or an error message.
     */
    public function getAllActivitiesLog($filteringData)
    {
        try {


            // Retrieve logs with filtering, caching, and pagination
            $activitiesLog = ActivitiesLog::with('user')
                ->when(!empty($filteringData), fn($query) => $query->filterBy($filteringData))
                ->orderByDesc('created_at')
                ->paginate(10);

            return $this->successResponse('تم استرجاع سجلات الأنشطة بنجاح.', 200, $activitiesLog);
        } catch (QueryException $e) {
            Log::error('خطأ في استعلام قاعدة البيانات عند استرجاع سجلات الأنشطة: ' . $e->getMessage());
            return $this->errorResponse('حدث خطا اثناء استرجاع سجلات الانشطة , يرجى المحاولة مرة اخر ');
        } catch (Exception $e) {
            Log::error('حدث خطأ عام أثناء استرجاع سجلات الأنشطة: ' . $e->getMessage());

            return $this->errorResponse('حدث خطا اثناء استرجاع سجلات الانشطة , يرجى المحاولة مرة اخر ');;
        }
    }
}
