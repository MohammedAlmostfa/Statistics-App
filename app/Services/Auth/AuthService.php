<?php

namespace App\Services\Auth;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Login a user.
     *
     * @param array $credentials User credentials: email, password.
     * @return array Contains message, status, and authorization details.
     */
    public function login($credentials)
    {
        try {
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return [
                    'status' => 401,
                    'message' => 'بيانات الاعتماد غير صحيحة.',
                ];
            }

            $user = JWTAuth::user();

            Log::error("Error during login: User status is '{$user->status}'");

            if (!isset($user->status)) {
                return [
                    'status' => 500,
                    'message' => 'حدث خطأ غير متوقع. حالة الحساب غير معروفة.',
                ];
            }

            if ($user->status === "محذوف") {
                return [
                    'status' => 403,
                    'message' => 'لقد تم حذف الحساب. يرجى التواصل مع المدير.',
                ];
            }

            return [
                'status' => 200,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'role' => $user->getRoleNames()->first(),
                ],
            ];
        } catch (Exception $e) {
            Log::error("Error during login for user: {$credentials['email']}. Exception: " . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء عملية تسجيل الدخول. يرجى إعادة المحاولة لاحقًا.',
            ];
        }
    }


    /**
     * Logout the authenticated user.
     *
     * @return array Contains message and status.
     */
    public function logout()
    {
        try {
            Auth::logout();

            return [
                'status' => 200,
                'message' => 'تم تسجيل الخروج بنجاح.',
            ];
        } catch (Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء عملية تسجيل الخروج. يرجى إعادة المحاولة لاحقًا.',
            ];
        }
    }

    /**
     * Refresh the JWT token for the authenticated user.
     *
     * @return array Contains message, status, user, and new token details.
     */
    public function refresh()
    {
        try {
            $newToken = Auth::refresh();
            $user = Auth::user();


            if ($user->status === "محذوف") {

                Auth::logout(true);
                return [
                    'status' => 403,
                    'message' => 'لقد تم حذف الحساب. يرجى التواصل مع المدير.',
                ];
            }

            return [
                'status' => 200,
                'message' => 'تم تحديث التوكن بنجاح.',
                'data' => [
                    'user' => $user,
                    'token' => $newToken,
                    'type' => 'bearer',
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error during token refresh: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث التوكن. يرجى إعادة المحاولة لاحقًا.',
            ];
        }
    }

}
