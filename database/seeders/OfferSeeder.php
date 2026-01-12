<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Welcome Discount',
                'code' => 'WELCOME20',
                'description' => 'Get 20% off on your first purchase',
                'type' => 'percentage',
                'discount_value' => 20,
                'minimum_purchase' => 50,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addMonths(1),
                'usage_limit' => 1000,
                'is_featured' => true,
            ],
            [
                'title' => 'Flat $10 Off',
                'code' => 'SAVE10',
                'description' => 'Get $10 off on orders above $100',
                'type' => 'fixed',
                'discount_value' => 10,
                'minimum_purchase' => 100,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'usage_limit' => 500,
                'is_featured' => true,
            ],
            [
                'title' => 'Free Shipping',
                'code' => 'FREESHIP',
                'description' => 'Free shipping on all orders',
                'type' => 'free_shipping',
                'discount_value' => null,
                'minimum_purchase' => 30,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(15),
                'usage_limit' => null,
            ],
            [
                'title' => 'Summer Sale',
                'code' => 'SUMMER25',
                'description' => '25% off on summer collection',
                'type' => 'percentage',
                'discount_value' => 25,
                'minimum_purchase' => 75,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'usage_limit' => 2000,
            ],
            [
                'title' => 'Buy One Get One Free',
                'code' => 'BOGO',
                'description' => 'Buy one item, get another free',
                'type' => 'buy_one_get_one',
                'discount_value' => null,
                'minimum_purchase' => null,
                'start_date' => Carbon::now()->subDays(3),
                'end_date' => Carbon::now()->addDays(20),
                'usage_limit' => 300,
                'is_featured' => true,
            ],
            [
                'title' => 'Expired Offer',
                'code' => 'EXPIRED50',
                'description' => '50% off (Expired)',
                'type' => 'percentage',
                'discount_value' => 50,
                'minimum_purchase' => 100,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subMonth(),
                'usage_limit' => 100,
                'is_active' => false,
            ],
        ];

        foreach ($offers as $offer) {
            Offer::create($offer);
        }

        $this->command->info('Offers seeded successfully!');
    }
}