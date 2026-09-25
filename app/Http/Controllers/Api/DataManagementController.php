<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\UserAppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataManagementController extends Controller
{
    public function __construct(
        private readonly UserAppSettingsService $settingsService,
        private readonly AuthService $authService,
    ) {}

    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();
        $payload = $this->settingsService->buildDataExport($user);
        $filename = 'grocery-user-data-'.$user->id.'-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authService->deleteAccount($user);

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }
}
