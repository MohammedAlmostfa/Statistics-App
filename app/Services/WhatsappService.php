<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
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
            $response = Http::get("https://api.ultramsg.com/" . env("INSTANCE_ID") . "/messages", [
                'token'  => env("API_TOKEN"),
                'page'   => $data['page'] ?? 1,
                'limit'  => $data['limit'] ?? 10,
                'to'     => isset($data['to']) ? $data['to'] . '@c.us' : null,
                'status' => $data['status'] ?? null,
            ]);
            $dataResponse = $response->json();
            Log::info('API Response:', $dataResponse);
            // استخراج بيانات `pagination`
            $pagination = [
                'total'        => $dataResponse['total'] ?? 0,
                'total_pages'  => $dataResponse['pages'] ?? 0,
                'per_page'     => $dataResponse['limit'] ?? 0,
                'current_page' => $dataResponse['page'] ?? 0,
            ];
            // Return success response with the data
            return [
                'status'  => 200,
                'message' => 'Messages fetched successfully',
                'data'    => $dataResponse,
                'pagination'=>$pagination
            ];
        } catch (Exception $e) {
            // If an error occurs, log the error message
            Log::error('Error in getMessage: ' . $e->getMessage());

            // Return an error response
            return [
                'status'  => 500,
                'message' => 'An error occurred while fetching messages. Please try again.',
                'data'    => null
            ];
        }
    }
}
