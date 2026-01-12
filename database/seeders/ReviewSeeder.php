<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Meal;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(10)->get();
        $meals = Meal::all();
        
        if ($users->isEmpty() || $meals->isEmpty()) {
            return;
        }
        
        $reviews = [];
        
        foreach ($meals as $meal) {
            // Create 3-5 reviews per meal
            $reviewCount = rand(3, 5);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users->random();
                
                // Ensure user doesn't review same meal twice
                if (!Review::where('user_id', $user->id)
                    ->where('meal_id', $meal->id)
                    ->exists()) {
                    
                    $reviews[] = [
                        'user_id' => $user->id,
                        'meal_id' => $meal->id,
                        'rating' => rand(3, 5),
                        'comment' => $this->generateReviewComment($meal->name),
                        'is_approved' => true,
                        'images' => json_encode($this->generateRandomImages()),
                        'created_at' => now()->subDays(rand(0, 30)),
                        'updated_at' => now()->subDays(rand(0, 30)),
                    ];
                }
            }
        }
        
        Review::insert($reviews);
    }
    
    private function generateReviewComment($mealName): string
    {
        $comments = [
            "Absolutely loved the $mealName! The flavors were perfectly balanced.",
            "The $mealName was delicious and very well presented. Highly recommended!",
            "One of the best $mealName I've ever had. Will definitely order again.",
            "Good $mealName, but could use a bit more seasoning in my opinion.",
            "Excellent $mealName! Fresh ingredients and amazing taste.",
            "The $mealName was good, but the portion size was smaller than expected.",
            "Absolutely amazing $mealName! The taste was out of this world.",
            "Very tasty $mealName. Perfect for a quick lunch.",
            "The $mealName was decent, but nothing extraordinary.",
            "Loved every bite of the $mealName! Will be coming back for more.",
        ];
        
        return $comments[array_rand($comments)];
    }
    
    private function generateRandomImages(): array
    {
        $images = [];
        $imageCount = rand(0, 3);
        
        for ($i = 0; $i < $imageCount; $i++) {
            $images[] = 'reviews/review_' . rand(1, 10) . '.jpg';
        }
        
        return $images;
    }
}