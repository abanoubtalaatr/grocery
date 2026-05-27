<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;

    public function definition(): array
    {
        /** @var Category|null $category */
        $category = Category::inRandomOrder()->first();

        /** @var Subcategory|null $subcategory */
        $subcategory = $category
            ? Subcategory::where('category_id', $category->id)->inRandomOrder()->first()
            : null;

        $brand = $this->faker->randomElement([
            'Fresh Farms', 'Green Valley', 'Harvest Hub', 'Nile Market', 'Baker’s Corner',
            'Tropical Delights', 'Ocean Catch', 'Butcher’s Choice', 'Healthy Life', 'Daily Essentials',
            'Cairo Dairy', 'Golden Wheat', 'Premium Pantry', 'Urban Grocers', 'Sunrise Foods',
        ]);

        $adjective = $this->faker->randomElement([
            'Fresh', 'Organic', 'Premium', 'Family Pack', 'Classic', 'New', 'Daily', 'Chef’s',
            'Extra', 'Light', 'Rich', 'Deluxe', 'Select', 'Signature',
        ]);
        $product = $this->faker->randomElement([
            'Tomatoes', 'Cucumbers', 'Spinach', 'Salad Mix', 'Potatoes', 'Carrots', 'Bell Peppers',
            'Bananas', 'Apples', 'Oranges', 'Berries', 'Mango', 'Pineapple',
            'Milk', 'Greek Yogurt', 'Cheddar Cheese', 'Butter',
            'Chicken Breast', 'Ground Beef', 'Beef Steak', 'Salmon Fillet',
            'Sourdough Bread', 'Croissants', 'Cookies', 'Cake Slice',
            'Coffee Beans', 'Green Tea', 'Orange Juice', 'Sparkling Water',
        ]);
        $variant = $this->faker->randomElement([
            '', '', '', ' (Large)', ' (Small)', ' (500g)', ' (1kg)', ' (2L)', ' (Pack of 6)',
        ]);

        $title = trim($adjective . ' ' . $product . $variant);
        $slugBase = Str::slug($title);

        // Stable, unique-ish image URL (doesn't require storage setup).
        $image = "https://picsum.photos/seed/{$slugBase}/600/600";

        $price = $this->faker->randomFloat(2, 1.25, 89.99);

        $hasOffer = $this->faker->boolean(35);
        $discountPercent = $hasOffer ? $this->faker->numberBetween(5, 35) : 0;
        $offerTitle = $hasOffer ? "{$discountPercent}% OFF" : null;
        $discountPrice = $hasOffer ? round($price * (1 - $discountPercent / 100), 2) : null;

        $rating = $this->faker->randomFloat(2, 3.6, 5.0);
        $ratingCount = $this->faker->numberBetween(0, 2500);

        $stock = $this->faker->numberBetween(0, 250);
        $sold = $this->faker->numberBetween(0, 15000);

        $isHot = $this->faker->boolean(18);
        $isFeatured = $this->faker->boolean(22);

        $availableDate = $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-10 days', '+3 days')->format('Y-m-d') : null;
        $expiryDate = $this->faker->boolean(75) ? $this->faker->dateTimeBetween('+1 days', '+25 days')->format('Y-m-d') : null;

        return [
            'category_id' => $category?->id,
            'subcategory_id' => $subcategory?->id,
            'title' => $title,
            // Meal model will also generate on creating, but we prefer deterministic here.
            'slug' => $slugBase . '-' . Str::lower(Str::random(6)),
            'description' => $this->faker->paragraphs(asText: true),
            'image' => $image,
            'offer_title' => $offerTitle,
            'price' => $price,
            'discount_price' => $discountPrice,
            'rating' => $rating,
            'rating_count' => $ratingCount,
            'size' => $this->faker->randomElement(['250g', '400g', '500g', '750g', '1kg', '2kg', '330ml', '500ml', '1L', '2L', 'Pack of 6', 'Pack of 12']),
            'expiry_date' => $expiryDate,
            'includes' => $this->faker->randomElement([
                '1 pack', '2 packs', '1 bottle', '2 bottles', '1 tray', '1 piece', '6 pieces', '12 pieces',
            ]),
            'how_to_use' => $this->faker->sentence(),
            'features' => implode(', ', $this->faker->unique()->words($this->faker->numberBetween(3, 6))),
            'brand' => $brand,
            'stock_quantity' => $stock,
            'sold_count' => $sold,
            'is_featured' => $isFeatured,
            'is_available' => true,
            'is_hot' => $isHot,
            'available_date' => $availableDate,
        ];
    }

    public function hot(): static
    {
        return $this->state(fn () => ['is_hot' => true]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}

