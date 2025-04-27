<?php

namespace App\Services\Auth;

use Exception;

use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthService
{



    /**
     * Login a user.
     *
     * This method authenticates a user using their email and password.
     * If successful, it returns a JWT token for further authenticated requests.
     *
     * @param array $credentials User credentials: email, password.
     * @return array Contains message, status, data, and authorization details.
     */
    public function login($credentials)
    {
        try {
            // Attempt to authenticate the user
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return [
                    'status' => 401,
                    'message' => 'الحساب غير موجود',
                ];
            }

            return [
                'status' => 201,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error in login: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ، يرجى إعادة المحاولة مرة أخرى',
            ];
        }
    }


    /**
     * Logout the authenticated user.
     *
     * This method logs out the currently authenticated user.
     *
     * @return array Contains message and status.
     */
    public function logout()
    {
        try {
            // Logout the user
            Auth::logout();
            return [
                'message' => __('auth.logout_success'),
                'status' => 200, // HTTP status code for success
            ];
        } catch (Exception $e) {
            // Log the error if logout fails
            Log::error('Error in logout: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => [
            'errorDetails' => 'حدذ خطا يرجا اعادة المحاولة مرة اخرى',
                ],
            ];
        }
    }

    /**
     * Refresh the JWT token for the authenticated user.
     *
     * This method refreshes the JWT token for the authenticated user.
     *
     * @return array Contains message, status, user, and authorization details.
     */
    public function refresh()
    {
        try {
            // Refresh the token for the authenticated user
            return [
                'message' => __('auth.token_refresh_success'),
                'status' => 200, // HTTP status code for success
                'data' => [
                    'user' => Auth::user(), // Return the authenticated user
                    'token' => Auth::refresh(), // Return the new token
                ],
            ];
        } catch (Exception $e) {
            // Log the error if token refresh fails
            Log::error('Error in token refresh: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => [
                         'errorDetails' => 'حدذ خطا يرجا اعادة المحاولة مرة اخرى',
                ],
            ];
        }
    }


}
