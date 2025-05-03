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
                'token'  => env("API_TOKEN"), // Fetch the token from the environment
                'page'   => $data['page'] ?? 1,  // Default to page 1 if not provided
                'limit'  => $data['limit'] ?? 100, // Default to 100 messages if not provided
             'to' => $data['to'] ?? null ? $data['to'] . '@c.us' : null, // Optional: recipient phone number
                'status' => $data['status'] ?? null, // Optional: message status
            ]);

            // Convert the response into a JSON array
            $responseData = $response->json();

            // Return success response with the data
            return [
                'status'  => 200,
                'message' => 'Messages fetched successfully',
                'data'    => $responseData,
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
