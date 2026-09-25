<?php

namespace App\Actions\SmartList;

use App\Models\SmartList;
use Illuminate\Support\Facades\Storage;

class CreateSmartListAction
{
    public function execute(int $userId, array $data): SmartList
    {
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        $data['user_id'] = $userId;
        $data['description'] = $data['description'] ?? '';

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $data['image']->store('smart-lists', 'public');
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }
}