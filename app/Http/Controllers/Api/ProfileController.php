<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country_code' => $user->country_code,
                    'profile_image' => $user->profile_image,
                    'profile_image_url' => $user->profile_image_url,
                    'email_verified' => $user->email_verified,
                    'phone_verified' => $user->phone_verified,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update profile image
     */
    public function updateImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $image = $request->file('image');
            $path = $image->store('profile-images', 'public');

            // Update user
            $user->update(['profile_image' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'data' => [
                    'profile_image' => $user->profile_image,
                    'profile_image_url' => $user->profile_image_url,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update profile information
     */
    public function updateInfo(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id), 'alpha_dash'],
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users')->ignore($user->id), 'regex:/^\+?[1-9]\d{1,14}$/'],
                'country_code' => ['sometimes', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update only provided fields
            $data = $request->only(['username', 'email', 'phone', 'country_code']);
            
            // Remove empty values
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update',
                ], 400);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country_code' => $user->country_code,
                    'profile_image_url' => $user->profile_image_url,
                    'updated_at' => $user->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile image to delete',
                ], 404);
            }

            // Delete image from storage
            if (Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Update user
            $user->update(['profile_image' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
