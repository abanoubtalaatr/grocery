<?php
namespace App\Actions\Meal;

use App\Models\User;
use App\Services\FrequencyService;

class GetFrequencyMealsAction
{
    public function __construct(protected FrequencyService $service) {}

    public function execute(User $user, ?string $type, ?int $subcategoryId = null): array
    {
        $frequencyType = in_array($type, FrequencyService::VALID_TYPES, true)
            ? $type
            : FrequencyService::FREQUENCY_WEEKLY;

        $meals = $this->service->getFrequentlyOrderedMeals($user, $frequencyType, 50, $subcategoryId);

        $meals->transform(function ($meal) {
            $meal->order_count = $meal->getAttribute('order_count');
            return $meal;
        });

        $responseData = [
            'frequency_type' => $frequencyType,
            'meals' => $meals,
        ];

        if ($subcategoryId !== null) {
            $responseData['subcategory_id'] = $subcategoryId;
        }

        return $responseData;
    }
}