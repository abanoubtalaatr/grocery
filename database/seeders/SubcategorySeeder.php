<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $subcategoriesData = [
            'Vegetables' => [
                ['name' => 'Leafy Greens', 'description' => 'Fresh lettuce, spinach, kale and more', 'order' => 1],
                ['name' => 'Root Vegetables', 'description' => 'Carrots, potatoes, beets and more', 'order' => 2],
                ['name' => 'Bell Peppers', 'description' => 'Red, green, yellow bell peppers', 'order' => 3],
                ['name' => 'Tomatoes', 'description' => 'Fresh tomatoes and cherry tomatoes', 'order' => 4],
            ],
            'Fruits' => [
                ['name' => 'Tropical Fruits', 'description' => 'Mango, pineapple, papaya and more', 'order' => 1],
                ['name' => 'Berries', 'description' => 'Strawberries, blueberries, raspberries', 'order' => 2],
                ['name' => 'Citrus', 'description' => 'Oranges, lemons, limes and more', 'order' => 3],
                ['name' => 'Apples & Pears', 'description' => 'Fresh apples and pears', 'order' => 4],
            ],
            'Dairy Products' => [
                ['name' => 'Milk', 'description' => 'Fresh milk and flavored milk', 'order' => 1],
                ['name' => 'Cheese', 'description' => 'Various types of cheese', 'order' => 2],
                ['name' => 'Yogurt', 'description' => 'Greek yogurt, flavored yogurt', 'order' => 3],
                ['name' => 'Butter & Cream', 'description' => 'Butter, cream cheese, sour cream', 'order' => 4],
            ],
            'Meat & Poultry' => [
                ['name' => 'Chicken', 'description' => 'Fresh chicken breasts, thighs and whole chicken', 'order' => 1],
                ['name' => 'Beef', 'description' => 'Steaks, ground beef and more', 'order' => 2],
                ['name' => 'Fish & Seafood', 'description' => 'Fresh fish and seafood', 'order' => 3],
                ['name' => 'Lamb', 'description' => 'Fresh lamb cuts', 'order' => 4],
            ],
            'Bakery' => [
                ['name' => 'Bread', 'description' => 'Fresh bread and rolls', 'order' => 1],
                ['name' => 'Pastries', 'description' => 'Croissants, danishes and more', 'order' => 2],
                ['name' => 'Cakes', 'description' => 'Fresh baked cakes', 'order' => 3],
                ['name' => 'Cookies', 'description' => 'Fresh baked cookies', 'order' => 4],
            ],
        ];

        foreach ($subcategoriesData as $categoryName => $subcategories) {
            $category = $categories->firstWhere('name', $categoryName);
            
            if ($category) {
                foreach ($subcategories as $subcategoryData) {
                    Subcategory::create([
                        'category_id' => $category->id,
                        'name' => $subcategoryData['name'],
                        'description' => $subcategoryData['description'],
                        'order' => $subcategoryData['order'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Subcategories seeded successfully!');
    }
}
