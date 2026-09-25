<?php

namespace App\Actions\Setting;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class UpdateSettingAction
{
    public function execute(Setting $setting, SettingRequest $request): Setting
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $setting->update($data);

        return $setting->fresh();
    }
}