<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;

class LargeMealSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid accidental re-seeding huge catalogs on environments where data already exists.
        if (Meal::query()->count() >= 500) {
            $this->command?->info('LargeMealSeeder skipped (meals already seeded).');
            return;
        }

        $count = (int) (env('MEALS_SEED_COUNT', 4000));
        if ($count < 0) {
            $count = 0;
        }
        if ($count > 20000) {
            $count = 20000;
        }

        // Chunked inserts keep memory reasonable for large runs.
        Meal::factory()
            ->count($count)
            ->create();

        $this->command?->info("LargeMealSeeder completed ({$count} meals).");
    }
}

