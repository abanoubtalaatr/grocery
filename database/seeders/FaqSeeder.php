<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        // Create some FAQs
        Faq::factory()->count(20)->create();

        // Or create with specific categories
        $this->createFaqsByCategory('General', 5);
        $this->createFaqsByCategory('Technical', 5);
        $this->createFaqsByCategory('Billing', 5);
        $this->createFaqsByCategory('Account', 5);
    }

    private function createFaqsByCategory(string $category, int $count): void
    {
        Faq::factory()
            ->count($count)
            ->category($category)
            ->create();
    }
}