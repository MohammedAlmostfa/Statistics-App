<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserService extends Service
{
    /**
     * Fetch all users from the database.
     *
     * This method retrieves a list of all users, including their id, name, status, and the date they were created.
     * It uses basic column selection for optimization purposes.
     *
     * @return array Response containing the status, message, and the list of users.
     */
    public function getAllUsers()
    {
        try {
            // Fetch all users with specified columns (id, name, status, created_at)
            $users = User::select('id', 'name', 'status', 'created_at')->where("status", 'موجود')->orderByDesc('created_at')->get();

            return [
                'status' => 200,
                'message' => 'تم جلب جميع المستخدمين بنجاح',
                'data' => $users,
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
     * This method creates a new user with the provided name and password. The password is encrypted using bcrypt before saving.
     *
     * @param array $data User data containing name and password.
     * @return array Response containing the status, message, and the created user data.
     */
    public function createUser($data)
    {
        try {
            // Create the user with encrypted password
            $user = User::create([
                'name' => $data['name'],
                'password' => bcrypt($data['password']),
            ]);
            $user->assignRole('Accountant');
            return [
                'status' => 201,
                'message' => 'تم إنشاء المستخدم بنجاح',
                'data' => $user,
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
     * Update an existing user's details.
     *
     * This method updates the user's name and password. The password is encrypted using bcrypt.
     *
     * @param array $data Updated user data containing the new name and password.
     * @param User $user The user model instance that will be updated.
     * @return array Response containing the status and message after the update.
     */
    public function updateUser($data, User $user)
    {
        try {
            // Update the user details with the provided data
            $user->update([
                'name' => $data['name'],
                'password' => bcrypt($data['password']),
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
     * Delete a user from the system.
     *
     * This method deletes a specific user from the database.
     *
     * @param User $user The user model instance that needs to be deleted.
     * @return array Response containing the status and message after deletion.
     */
    public function deleteUser(User $user)
    {
        try {
            if ($user->hasRole("Admin")) {
                return [
                    'status' => 403,
                    'message' => 'لا يمكن حذف المدير',
                ];
            }
            $user->update([
                'status' => 'محذوف',
            ]);

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
