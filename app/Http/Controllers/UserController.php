<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\UserRequest\StoreUserData;
use App\Http\Requests\UserRequest\UpdateStatus;
use App\Http\Requests\UserRequest\UpdateUserData;

class UserController extends Controller
{
    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected $userService;

    /**
     * Create a new UserController instance.
     *
     * @param UserService $userService The user service used to handle logic.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $this->authorize('getUser', User::class);

        // Create the user using UserService
        $result = $this->userService->getAllUsers();

        // Return response based on the result
        return $result['status'] === 200
            ? self::success($result['data'], $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    /**
     * Store a new user.
     *
     * @param StoreUserData $request Validated user data.
     * @return \Illuminate\Http\JsonResponse JSON response with status and message.
     */
    public function store(StoreUserData $request)
    {
        $this->authorize('createUser', User::class);

        $validatedData = $request->validated();

        $result = $this->userService->createUser($validatedData);
        // Return response based on the result
        return $result['status'] === 201
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    public function update(UpdateUserData $request, User $user)
    {
        $this->authorize('updateUser', User::class);

        // Validate and get the input data
        $validatedData = $request->validated(); // Corrected: use `validated` method

        // Create the user using UserService
        $result = $this->userService->updateUser($validatedData, $user);

        // Return response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    public function destroy(User $user)
    {
        $this->authorize('deleteUser', User::class);
        // Create the user using UserService
        $result = $this->userService->deleteUser($user);

        // Return response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
    public function updateUserStatus(UpdateStatus $request, User $user)
    {
        $this->authorize('changeStatusUser', User::class);
        $validatedData = $request->validated();

        $result = $this->userService->updateUserStatus($validatedData, $user);

        // Return response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }
}
