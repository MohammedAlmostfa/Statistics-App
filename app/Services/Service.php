<?php

namespace App\Services;

/**
 * BaseService: Provides common response methods for all services.
 */
class Service
{
    /**
     * Generate a standardized success response.
     *
     * @param string $message Success message.
     * @param int $status HTTP status code (default is 200).
     * @param mixed|null $data Optional data payload.
     * @return array Structured response.
     */
    protected function successResponse(string $message, int $status = 200, $data = null): array
    {
        return [
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
    }

    /**
     * Generate a standardized error response.
     *
     * @param string $message Error message.
     * @param int $status HTTP status code (default is 500).
     * @return array Structured response.
     */
    protected function errorResponse(string $message, int $status = 500): array
    {
        return [
            'message' => $message,
            'status' => $status,
        ];
    }
}
