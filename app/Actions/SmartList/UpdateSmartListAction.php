<?php

namespace App\Actions\SmartList;

use App\Models\SmartList;
use Illuminate\Support\Facades\Storage;

class UpdateSmartListAction
{
    public function execute(SmartList $smartList, array $data): SmartList
    {
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            if ($smartList->image) {
                Storage::disk('public')->delete($smartList->image);
            }
            $data['image'] = $data['image']->store('smart-lists', 'public');
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }
}