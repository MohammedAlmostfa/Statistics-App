<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService extends Service
{
    /**
     * Fetch messages from the API
     *
     * @param array $data Filter data such as page, limit, to, and status
     *
     * @return array Response with status 200 or 500
     */
    public function getMessage($data)
    {
        try {
            // Send a GET request to the API with the provided filter data
            $response = Http::get("https://api.ultramsg.com/" . config("services.ultramsg.instance_id") . "/messages", [
                'token'  => config("services.ultramsg.api_token"),
                'page'   => $data['page'] ?? 1,
                'limit'  => $data['limit'] ?? 10,
                'to'     => isset($data['to']) ? $data['to'] . '@c.us' : null,
                'status' => $data['status'] ?? null,
            ]);

            // Handle API failure
            if ($response->failed()) {
                Log::error('فشل طلب API بالحالة: ' . $response->status());
                return [
                    'status'  => 500,
                    'message' => 'فشل في جلب رسائل الواتس اب.',
                    'data'    => null
                ];
            }

            $dataResponse = $response->json();
            Log::info('API Response:', $dataResponse);

            // Pagination data extraction
            $pagination = [
                'total'        => $dataResponse['total'] ?? 0,
                'total_pages'  => $dataResponse['pages'] ?? 0,
                'per_page'     => $dataResponse['limit'] ?? 0,
                'current_page' => $dataResponse['page'] ?? 0,
            ];

            return [
                 'status'     => 200,
                 'message'    => 'تم جلب الرسائل بنجاح.',
                 'data'       => $dataResponse,
                 'pagination' => $pagination
             ];

        } catch (Exception $e) {
            Log::error('خطأ في getMessage: ' . $e->getMessage());

            return [
                'status'  => 500,
                'message' => 'حدث خطأ أثناء جلب الرسائل، يرجى المحاولة لاحقًا.',
                'data'    => null
            ];
        }
    }
}
