<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vegetables = Category::where('slug', 'vegetables')->first();
        $fruits = Category::where('slug', 'fruits')->first();
        $dairy = Category::where('slug', 'dairy-products')->first();
        $meat = Category::where('slug', 'meat-poultry')->first();
        $bakery = Category::where('slug', 'bakery')->first();

        // Get subcategories
        $leafyGreens = Subcategory::where('name', 'Leafy Greens')->first();
        $tropicalFruits = Subcategory::where('name', 'Tropical Fruits')->first();
        $berries = Subcategory::where('name', 'Berries')->first();
        $chicken = Subcategory::where('name', 'Chicken')->first();
        $beef = Subcategory::where('name', 'Beef')->first();
        $yogurt = Subcategory::where('name', 'Yogurt')->first();
        $bread = Subcategory::where('name', 'Bread')->first();
        $pastries = Subcategory::where('name', 'Pastries')->first();

        $meals = [
            [
                'category_id' => $vegetables?->id,
                'subcategory_id' => $leafyGreens?->id,
                'title' => 'Fresh Organic Salad Mix',
                'description' => 'A delightful mix of fresh organic vegetables including lettuce, tomatoes, cucumbers, and carrots. Perfect for healthy meals.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400',
                'offer_title' => '20% OFF Today',
                'price' => 12.99,
                'discount_price' => 10.39,
                'rating' => 4.5,
                'rating_count' => 127,
                'size' => '500g',
                'expiry_date' => Carbon::now()->addDays(3),
                'includes' => '1 pack (500g)',
                'how_to_use' => 'Wash thoroughly before use. Perfect for salads, wraps, and sandwiches.',
                'features' => 'Organic, Fresh, Locally sourced',
                'brand' => 'Fresh Farms',
                'stock_quantity' => 50,
                'sold_count' => 234,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $vegetables?->id,
                'subcategory_id' => null,
                'title' => 'Grilled Vegetable Platter',
                'description' => 'Assorted grilled vegetables including bell peppers, zucchini, eggplant, and mushrooms with herbs.',
                'image' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=400',
                'offer_title' => 'Special Deal',
                'price' => 15.99,
                'discount_price' => 12.99,
                'rating' => 4.7,
                'rating_count' => 89,
                'size' => '800g',
                'expiry_date' => Carbon::now()->addDays(2),
                'includes' => '1 tray (800g)',
                'how_to_use' => 'Ready to eat. Can be reheated in microwave or oven.',
                'features' => 'Pre-cooked, Seasoned, Gluten-free',
                'brand' => 'Gourmet Kitchen',
                'stock_quantity' => 30,
                'sold_count' => 156,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $fruits?->id,
                'subcategory_id' => $tropicalFruits?->id,
                'title' => 'Tropical Fruit Bowl',
                'description' => 'Fresh tropical fruits including mango, pineapple, papaya, and dragon fruit. A taste of paradise!',
                'image' => 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?w=400',
                'offer_title' => 'Fresh Today',
                'price' => 18.99,
                'discount_price' => 14.99,
                'rating' => 4.8,
                'rating_count' => 203,
                'size' => '1kg',
                'expiry_date' => Carbon::now()->addDays(2),
                'includes' => '1 bowl (approximately 1kg mixed fruits)',
                'how_to_use' => 'Wash and enjoy fresh. Perfect for breakfast or dessert.',
                'features' => 'Fresh cut, No preservatives, High in vitamins',
                'brand' => 'Tropical Delights',
                'stock_quantity' => 45,
                'sold_count' => 312,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $fruits?->id,
                'subcategory_id' => $berries?->id,
                'title' => 'Berry Medley',
                'description' => 'A delicious mix of strawberries, blueberries, raspberries, and blackberries. Rich in antioxidants.',
                'image' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=400',
                'offer_title' => null,
                'price' => 16.99,
                'discount_price' => null,
                'rating' => 4.6,
                'rating_count' => 145,
                'size' => '400g',
                'expiry_date' => Carbon::now()->addDays(3),
                'includes' => '1 punnet (400g mixed berries)',
                'how_to_use' => 'Wash gently before eating. Great for smoothies, yogurt toppings, or snacking.',
                'features' => 'Fresh, Antioxidant-rich, Premium quality',
                'brand' => 'Berry Best',
                'stock_quantity' => 60,
                'sold_count' => 189,
                'is_featured' => false,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $meat?->id,
                'subcategory_id' => $chicken?->id,
                'title' => 'Grilled Chicken Breast',
                'description' => 'Tender grilled chicken breast marinated in herbs and spices. High protein, low fat.',
                'image' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400',
                'offer_title' => '15% OFF',
                'price' => 22.99,
                'discount_price' => 19.54,
                'rating' => 4.4,
                'rating_count' => 176,
                'size' => '500g',
                'expiry_date' => Carbon::now()->addDays(5),
                'includes' => '2 pieces (approximately 500g)',
                'how_to_use' => 'Pre-cooked. Heat in oven at 180°C for 10 minutes or microwave for 2-3 minutes.',
                'features' => 'High protein, Low fat, Pre-seasoned, Gluten-free',
                'brand' => 'Premium Meats',
                'stock_quantity' => 40,
                'sold_count' => 267,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $meat?->id,
                'subcategory_id' => $beef?->id,
                'title' => 'Beef Steak Premium Cut',
                'description' => 'Premium quality beef steak, perfectly seasoned and grilled to perfection.',
                'image' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=400',
                'offer_title' => null,
                'price' => 29.99,
                'discount_price' => null,
                'rating' => 4.9,
                'rating_count' => 98,
                'size' => '300g',
                'expiry_date' => Carbon::now()->addDays(4),
                'includes' => '1 steak (approximately 300g)',
                'how_to_use' => 'Best served medium-rare. Grill or pan-fry for 3-4 minutes each side.',
                'features' => 'Premium cut, Aged beef, Grass-fed, Hormone-free',
                'brand' => 'Butcher\'s Choice',
                'stock_quantity' => 25,
                'sold_count' => 134,
                'is_featured' => false,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $dairy?->id,
                'subcategory_id' => $yogurt?->id,
                'title' => 'Greek Yogurt Parfait',
                'description' => 'Creamy Greek yogurt layered with fresh berries, honey, and granola.',
                'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400',
                'offer_title' => 'Healthy Choice',
                'price' => 8.99,
                'discount_price' => 6.99,
                'rating' => 4.7,
                'rating_count' => 221,
                'size' => '350ml',
                'expiry_date' => Carbon::now()->addDays(7),
                'includes' => '1 cup (350ml) with toppings',
                'how_to_use' => 'Ready to eat. Keep refrigerated. Best consumed chilled.',
                'features' => 'High protein, Probiotic, No artificial flavors, Low sugar',
                'brand' => 'Healthy Life',
                'stock_quantity' => 75,
                'sold_count' => 389,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $bakery?->id,
                'subcategory_id' => $bread?->id,
                'title' => 'Artisan Bread Selection',
                'description' => 'Freshly baked artisan bread including sourdough, whole wheat, and multigrain.',
                'image' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=400',
                'offer_title' => 'Baked Today',
                'price' => 7.99,
                'discount_price' => null,
                'rating' => 4.5,
                'rating_count' => 167,
                'size' => '600g',
                'expiry_date' => Carbon::now()->addDays(3),
                'includes' => '1 loaf (600g)',
                'how_to_use' => 'Best served fresh or toasted. Store in breadbox or freeze for later use.',
                'features' => 'Freshly baked, No preservatives, Artisan quality',
                'brand' => 'Baker\'s Corner',
                'stock_quantity' => 55,
                'sold_count' => 298,
                'is_featured' => true,
                'available_date' => Carbon::today(),
            ],
            [
                'category_id' => $bakery?->id,
                'subcategory_id' => $pastries?->id,
                'title' => 'Croissant & Pastry Box',
                'description' => 'Assorted French pastries including croissants, pain au chocolat, and Danish pastries.',
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400',
                'offer_title' => null,
                'price' => 14.99,
                'discount_price' => null,
                'rating' => 4.8,
                'rating_count' => 192,
                'size' => '6 pieces',
                'expiry_date' => Carbon::now()->addDays(2),
                'includes' => '6 assorted pastries',
                'how_to_use' => 'Best served warm. Heat in oven at 160°C for 5 minutes.',
                'features' => 'Butter pastry, French recipe, Freshly baked',
                'brand' => 'French Patisserie',
                'stock_quantity' => 35,
                'sold_count' => 245,
                'is_featured' => false,
                'available_date' => Carbon::today(),
            ],
        ];

        foreach ($meals as $meal) {
            if ($meal['category_id']) {
                Meal::create($meal);
            }
        }

        $this->command->info('Meals seeded successfully with enhanced details!');
    }
}
