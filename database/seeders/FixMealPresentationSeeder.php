<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;

class FixMealPresentationSeeder extends Seeder
{
    public function run(): void
    {
        $foodImages = [
            'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1506806732259-39c2d0268443?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1559181567-c3190ca9959b?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=1200&auto=format&fit=crop&q=80',
        ];

        $updatedImages = 0;
        $updatedRatings = 0;

        Meal::query()
            ->select(['id', 'image', 'rating', 'rating_count'])
            ->chunkById(500, function ($rows) use ($foodImages, &$updatedImages, &$updatedRatings) {
                foreach ($rows as $meal) {
                    $dirty = false;

                    $img = (string) ($meal->image ?? '');
                    if ($img === '' || str_contains($img, 'picsum.photos')) {
                        $meal->image = $foodImages[array_rand($foodImages)];
                        $dirty = true;
                        $updatedImages++;
                    }

                    if ((float) $meal->rating <= 0) {
                        $meal->rating = round(mt_rand(38, 50) / 10, 2); // 3.8 - 5.0
                        $dirty = true;
                        $updatedRatings++;
                    }

                    if ((int) $meal->rating_count <= 0) {
                        $meal->rating_count = mt_rand(3, 3200);
                        $dirty = true;
                    }

                    if ($dirty) {
                        $meal->save();
                    }
                }
            });

        $this->command?->info("FixMealPresentationSeeder updated images={$updatedImages}, ratings={$updatedRatings}.");
    }
}

