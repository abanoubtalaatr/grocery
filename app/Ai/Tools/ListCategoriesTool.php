<?php

namespace App\Ai\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListCategoriesTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get all available product categories with the number of products in each. Use this when the user asks what types of products or categories are available in the store.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('meals')
            ->get(['id', 'name', 'description']);

        if ($categories->isEmpty()) {
            return json_encode(['found' => false, 'message' => 'No categories available.'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'found' => true,
            'categories' => $categories->map(fn ($cat) => [
                'name' => $cat->name,
                'description' => $cat->description,
                'products_count' => $cat->meals_count,
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
