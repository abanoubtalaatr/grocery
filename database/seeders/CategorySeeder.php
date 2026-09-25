<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vegetables',
                'description' => 'Fresh organic vegetables',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400',
                'sort_order' => 1,
            ],
            [
                'name' => 'Fruits',
                'description' => 'Fresh seasonal fruits',
                'image' => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dairy Products',
                'description' => 'Milk, cheese, yogurt and more',
                'image' => 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=400',
                'sort_order' => 3,
            ],
            [
                'name' => 'Meat & Poultry',
                'description' => 'Fresh meat and poultry products',
                'image' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400',
                'sort_order' => 4,
            ],
            [
                'name' => 'Bakery',
                'description' => 'Fresh bread and baked goods',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400',
                'sort_order' => 5,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Drinks and refreshments',
                'image' => 'https://images.unsplash.com/photo-1437418747212-8d9709afab22?w=400',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'image' => $category['image'],
                'sort_order' => $category['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
