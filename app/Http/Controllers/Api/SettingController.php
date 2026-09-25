<?php

namespace App\Http\Controllers\Api;

use App\Actions\Setting\UpdateSettingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\Api\SettingResource;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ResponseTrait;

    /**
     * Get settings
     */
    public function index(): JsonResponse
    {
        $settings = Setting::getSettings();

        return $this->successResponse(
            new SettingResource($settings),
            'Settings retrieved successfully'
        );
    }

    /**
     * Update settings
     */
    public function update(SettingRequest $request, UpdateSettingAction $action): JsonResponse
    {
        $settings = Setting::getSettings();

        $updatedSettings = $action->execute($settings, $request);

        return $this->successResponse(
            new SettingResource($updatedSettings),
            'Settings updated successfully'
        );
    }

    /**
     * Get specific settings for public use
     */
    public function publicSettings(): JsonResponse
    {
        $settings = Setting::getSettings();

        return $this->successResponse(
            new SettingResource($settings),
            'Public settings retrieved successfully'
        );
    }
}