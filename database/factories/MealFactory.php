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

        /**
         * Food-only, high-quality images (stable URLs).
         * We intentionally keep a curated list so the demo always looks great.
         */
        $foodImages = [
            // Fresh produce
            'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1200&auto=format&fit=crop&q=80', // veggies
            'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=1200&auto=format&fit=crop&q=80', // salad
            'https://images.unsplash.com/photo-1506806732259-39c2d0268443?w=1200&auto=format&fit=crop&q=80', // tomatoes
            'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?w=1200&auto=format&fit=crop&q=80', // apples
            'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=1200&auto=format&fit=crop&q=80', // oranges
            'https://images.unsplash.com/photo-1547514701-42782101795e?w=1200&auto=format&fit=crop&q=80', // bananas
            'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1200&auto=format&fit=crop&q=80', // berries
            'https://images.unsplash.com/photo-1559181567-c3190ca9959b?w=1200&auto=format&fit=crop&q=80', // fruit bowl
            'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=1200&auto=format&fit=crop&q=80', // pineapple
            'https://images.unsplash.com/photo-1528825871115-3581a5387919?w=1200&auto=format&fit=crop&q=80', // mango

            // Dairy
            'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=1200&auto=format&fit=crop&q=80', // dairy
            'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=1200&auto=format&fit=crop&q=80', // milk
            'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=1200&auto=format&fit=crop&q=80', // yogurt parfait
            'https://images.unsplash.com/photo-1559561854-8bff5f86b76f?w=1200&auto=format&fit=crop&q=80', // cheese board
            'https://images.unsplash.com/photo-1580915411954-282cb1d17c4a?w=1200&auto=format&fit=crop&q=80', // butter

            // Meat / seafood
            'https://images.unsplash.com/photo-1604908554162-45f8a43b1784?w=1200&auto=format&fit=crop&q=80', // chicken
            'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=1200&auto=format&fit=crop&q=80', // steak
            'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=1200&auto=format&fit=crop&q=80', // salmon
            'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=1200&auto=format&fit=crop&q=80', // seafood

            // Bakery / sweets
            'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1200&auto=format&fit=crop&q=80', // bread
            'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=1200&auto=format&fit=crop&q=80', // artisan bread
            'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=1200&auto=format&fit=crop&q=80', // pastries
            'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=1200&auto=format&fit=crop&q=80', // coffee
            'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?w=1200&auto=format&fit=crop&q=80', // cookies
            'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=1200&auto=format&fit=crop&q=80', // cake

            // Drinks
            'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=1200&auto=format&fit=crop&q=80', // juice
            'https://images.unsplash.com/photo-1510626176961-4b57d4fbad03?w=1200&auto=format&fit=crop&q=80', // tea
            'https://images.unsplash.com/photo-1528823872057-9c018a7bd6a6?w=1200&auto=format&fit=crop&q=80', // sparkling water
        ];

        $image = $this->faker->randomElement($foodImages);

        $price = $this->faker->randomFloat(2, 1.25, 89.99);

        $hasOffer = $this->faker->boolean(35);
        $discountPercent = $hasOffer ? $this->faker->numberBetween(5, 35) : 0;
        $offerTitle = $hasOffer ? "{$discountPercent}% OFF" : null;
        $discountPrice = $hasOffer ? round($price * (1 - $discountPercent / 100), 2) : null;

        // Ratings that look realistic for a storefront demo.
        $rating = $this->faker->randomFloat(2, 3.8, 5.0);
        $ratingCount = $this->faker->numberBetween(3, 3200);

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

