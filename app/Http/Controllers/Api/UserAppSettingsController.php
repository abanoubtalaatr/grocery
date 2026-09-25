<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserAppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'language' => ['required', 'string', 'in:en,ar'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => $this->settingsService->updateLanguage(
                $request->user(),
                (string) $data['language'],
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
        $data = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appearance updated successfully',
            'data' => $this->settingsService->updateAppearance(
                $request->user(),
                (string) $data['theme'],
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
        $data = $request->validate([
            'order_updates' => ['sometimes', 'boolean'],
            'promotion_emails' => ['sometimes', 'boolean'],
            'nutrition_insights' => ['sometimes', 'boolean'],
            'price_alerts' => ['sometimes', 'boolean'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'data' => $this->settingsService->updateNotificationPreferences(
                $request->user(),
                $data,
            ),
        ]);
    }
}
