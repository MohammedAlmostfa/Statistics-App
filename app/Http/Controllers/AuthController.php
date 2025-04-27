<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Psr7\Request;
use App\Services\Auth\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\StorProfileRequest;
use App\Http\Requests\AuthRequest\ResendCode;
use App\Http\Requests\AuthRequest\LoginRequest;
use App\Http\Requests\AuthRequest\RegisterRequest;
use App\Http\Requests\AuthRequest\GoogelloginRequest;
use App\Http\Requests\AuthRequest\SetLocationData;
use App\Http\Requests\AuthRequest\VerficationRequest;

/**
 * Class AuthController
 *
 * Handles authentication-related operations, including registration, login, verification, and token management.
 */
class AuthController extends Controller
{
    /**
     * The authentication service instance.
     *
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new AuthController instance.
     *
     * @param AuthService $authService The authentication service used to handle logic.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    /**
     * Log in an existing user.
     *
     * Validates credentials and returns a JWT token on successful authentication.
     *
     * @param LoginRequest $request The request containing user credentials.
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Authenticate user via AuthService
        $result = $this->authService->login($credentials);

        return $result['status'] === 200
            ? self::success($result['data'], $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

    /**
     * Logout the authenticated user.
     *
     * Destroys the user's session and invalidates the JWT token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $result = $this->authService->logout();

        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }

    /**
     * Refresh the JWT token.
     *
     * Generates a new token if the current one is expired.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $result = $this->authService->refresh();

        return $result['status'] === 200
            ? self::success($result['data'], $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }


}
