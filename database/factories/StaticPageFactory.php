<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StaticPageFactory extends Factory
{
    public function definition(): array
    {
        $pages = [
            'terms-and-conditions' => 'Terms and Conditions',
            'privacy-policy' => 'Privacy Policy',
            'about-us' => 'About Us',
            'refund-policy' => 'Refund Policy',
            'shipping-policy' => 'Shipping Policy'
        ];

        $slug = $this->faker->randomElement(array_keys($pages));
        $title = $pages[$slug];

        return [
            'slug' => $slug,
            'title' => $title,
            'content' => $this->faker->paragraphs(10, true),
            'meta_title' => $title . ' | ' . config('app.name'),
            'meta_description' => $this->faker->sentence(15),
            'meta_keywords' => $this->faker->words(5),
            'is_published' => true,
            'order' => $this->faker->numberBetween(1, 10)
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function specificPage(string $slug, string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug,
            'title' => $title,
        ]);
    }
}