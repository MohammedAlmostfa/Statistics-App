<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WhatsappService extends Service
{
    public function getMessage(array $data)
    {
        try {
            $query = NotificationLog::query();

            if (!empty($data['status'])) {
                $query->where('status', $data['status']);
            }


            if (!empty($data['name'])) {
                $name = strtolower($data['name']);
                $query->whereHas('customer', function ($q) use ($name) {
                    $q->where(DB::raw('LOWER(name)'), 'like', "%{$name}%");
                });
            }


            $messages = $query->with('customer')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return [
                'status' => 200,
                'message' => 'تم جلب الرسائل بنجاح',
                'data' => $messages
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في جلب الرسائل: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب الرسائل، يرجى المحاولة مرة أخرى.',
                'data' => null
            ];
        }
    }
}
