<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserAppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAppSettingsController extends Controller
{
    public function __construct(
        private readonly UserAppSettingsService $settingsService,
    ) {}

    public function showLanguage(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingsService->getLanguage($request->user()),
        ]);
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'language' => ['required', 'string', 'in:en,ar'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => $this->settingsService->updateLanguage(
                $request->user(),
                (string) $request->input('language'),
            ),
        ]);
    }

    public function showAppearance(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingsService->getAppearance($request->user()),
        ]);
    }

    public function updateAppearance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Appearance updated successfully',
            'data' => $this->settingsService->updateAppearance(
                $request->user(),
                (string) $request->input('theme'),
            ),
        ]);
    }

    public function showNotificationPreferences(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingsService->getNotificationPreferences($request->user()),
        ]);
    }

    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_updates' => ['sometimes', 'boolean'],
            'promotion_emails' => ['sometimes', 'boolean'],
            'nutrition_insights' => ['sometimes', 'boolean'],
            'price_alerts' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'data' => $this->settingsService->updateNotificationPreferences(
                $request->user(),
                $validator->validated(),
            ),
        ]);
    }
}
