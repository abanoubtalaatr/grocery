<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get settings
     */
    public function index(): JsonResponse
    {
        $settings = Setting::getSettings();
        return response()->json([
            'success' => true,
            'data' => new SettingResource($settings)
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        $settings = Setting::getSettings();

        $data = $request->validate([
            'site_name' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'nullable', 'string'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'facebook' => ['sometimes', 'nullable', 'url', 'max:255'],
            'linkedin' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'url', 'max:255'],
            'twitter' => ['sometimes', 'nullable', 'url', 'max:255'],
            'copyright_text' => ['sometimes', 'nullable', 'string', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'favicon' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ]);
        
        // Handle file uploads if needed
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }
        
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }
        
        $settings->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => new SettingResource($settings)
        ]);
    }

    /**
     * Get specific settings for public use
     */
    public function publicSettings(): JsonResponse
    {
        $settings = Setting::getSettings();
        
        return response()->json([
            'site_name' => $settings->site_name,
            'site_description' => $settings->site_description,
            'social_media' => [
                'facebook' => $settings->facebook,
                'linkedin' => $settings->linkedin,
                'instagram' => $settings->instagram,
                'twitter' => $settings->twitter,
            ],
            'contact' => [
                'email' => $settings->email,
                'phone' => $settings->phone,
                'address' => $settings->address,
            ],
            'logo' => $settings->logo,
            'copyright' => $settings->copyright_text,
        ]);
    }
}