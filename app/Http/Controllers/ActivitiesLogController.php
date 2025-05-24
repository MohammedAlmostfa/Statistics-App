<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivitiesLog;
use Illuminate\Http\JsonResponse;
use App\Services\ActivitiesLogService;
use App\Http\Resources\ActivityLogResource;
use App\Http\Requests\FiltteringActivtiesLogData;

class ActivitiesLogController extends Controller
{
    protected $activitiesLogService;
    public function __construct(ActivitiesLogService $activitiesLogService)
    {
        $this->activitiesLogService = $activitiesLogService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(FiltteringActivtiesLogData $request): JsonResponse
    {
        $this->authorize('GetActivitesLog', ActivitiesLog::class);

        $result = $this->activitiesLogService->getAllActivitiesLog($request->validated());

        return $result['status'] === 200
            ? self::paginated($result['data'], ActivityLogResource::class, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }


}
