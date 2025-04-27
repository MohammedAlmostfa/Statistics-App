<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Fetch all users.
     *
     * @return array Response with status, message, and users data.
     */
    public function getAllUsers()
    {
        try {
            // Fetch all users with specified columns
            $users = User::select('id', 'name', 'created_at')->get();

            return [
                'status' => 200,
                'message' => 'تم جلب جميع المستخدمين بنجاح',
                'data' => $users, // Return fetched users
            ];
        } catch (Exception $e) {
            Log::error('Error in getAllUsers: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء جلب المستخدمين. يرجى إعادة المحاولة.',
            ];
        }
    }

    /**
     * Create a new user.
     *
     * @param array $data User data (name and password).
     * @return array Response with status and message or data.
     */
    public function createUser($data)
    {
        try {
            // Create the user with encrypted password
            $user = User::create([
                'name' => $data['name'],
                'password' => bcrypt($data['password']), // Encrypt the password
            ]);

            return [
                'status' => 201, // HTTP status code for resource creation
                'message' => 'تم إنشاء المستخدم بنجاح',
                'data' => $user, // Return created user
            ];
        } catch (Exception $e) {
            Log::error('Error in createUser: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء إنشاء المستخدم. يرجى إعادة المحاولة.',
            ];
        }
    }

    /**
     * Update an existing user.
     *
     * @param array $data Updated user data.
     * @param User $user User model instance.
     * @return array Response with status and message.
     */
    public function updateUser($data, User $user)
    {
        try {
            // Update user data with encrypted password
            $user->update([
                'name' => $data['name'],
                'password' => bcrypt($data['password']), // Encrypt the password
            ]);

            return [
                'status' => 200,
                'message' => 'تم تحديث بيانات المستخدم بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Error in updateUser: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء تحديث بيانات المستخدم. يرجى إعادة المحاولة.',
            ];
        }
    }

    /**
     * Delete a user.
     *
     * @param User $user User model instance.
     * @return array Response with status and message.
     */
    public function deleteUser(User $user)
    {
        try {
            // Delete the user
            $user->delete();

            return [
                'status' => 200,
                'message' => 'تم حذف المستخدم بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Error in deleteUser: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'حدث خطأ أثناء حذف المستخدم. يرجى إعادة المحاولة.',
            ];
        }
    }
}
