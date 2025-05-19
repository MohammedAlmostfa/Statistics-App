<?php

namespace App\Services;

use App\Models\ActivitiesLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class ActivitiesLogService extends Service
{
    public function getAllActivitiesLog($filteringData)
    {
        try {
            $page = request('page', 1);

            $cacheKey = 'activities_logs' . $page . (empty($filteringData) ? '' : md5(json_encode($filteringData)));

            $cacheKeys = Cache::get('activities', []);

            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('activities', $cacheKeys, now()->addHours(2));
            }


            $activitiesLog = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($filteringData) {
                return ActivitiesLog::with('user')
                    ->when(!empty($filteringData), fn ($query) => $query->filterBy($filteringData))
                    ->orderByDesc('created_at')
                    ->paginate(10);
            });

            return $this->successResponse('تم جلب سجلات الأنشطة بنجاح.', 200, $activitiesLog);
        } catch (QueryException $e) {
            Log::error('Database query error: ' . $e->getMessage());
            return $this->errorResponse('فشل في جلب سجلات الأنشطة.');
        } catch (Exception $e) {
            Log::error('General error retrieving activity logs: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء جلب سجلات الأنشطة.');
        }
    }

}
