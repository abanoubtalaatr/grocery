<?php

namespace App\Ai\Tools;

use App\Models\Meal;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProductsTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for grocery products by keyword, brand, category, or filters like in-stock or on-sale. Use this whenever the user asks about available products, prices, recommendations, or what is in a category.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        
        $args = $request->all();
        $query = Meal::with(['category', 'subcategory'])->available();

        if (! empty($args['query'])) {
            $keyword = $args['query'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('brand', 'like', "%{$keyword}%");
            });
        }

        if (! empty($args['category'])) {
            $categoryName = $args['category'];
            $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$categoryName}%"));
        }

        if (! empty($args['in_stock_only'])) {
            $query->inStock();
        }

        if (! empty($args['on_sale'])) {
            $query->withActiveDiscount();
        }

        if (! empty($args['featured'])) {
            $query->featured();
        }

        $limit = min(max((int) ($args['limit'] ?? 8), 1), 20);

        $meals = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('sold_count')
            ->limit($limit)
            ->get();

        if ($meals->isEmpty()) {
            return json_encode(['found' => false, 'message' => 'No products found matching the criteria.'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'found' => true,
            'count' => $meals->count(),
            'products' => $meals->map(fn ($meal) => [
                'id' => $meal->id,
                'name' => $meal->title,
                'price' => (float) $meal->price,
                'discount_price' => $meal->resolved_discount_price !== null ? (float) $meal->resolved_discount_price : null,
                'final_price' => (float) $meal->final_price,
                'category' => $meal->category?->name,
                'subcategory' => $meal->subcategory?->name,
                'brand' => $meal->brand,
                'rating' => (float) $meal->rating,
                'rating_count' => (int) $meal->rating_count,
                'in_stock' => $meal->isInStock(),
                'stock_quantity' => $meal->stock_quantity,
                'offer' => $meal->offer_title,
                'is_featured' => $meal->is_featured,
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Search keyword: product name, ingredient, or brand'),
            'category' => $schema->string()->description('Filter by category name (e.g. Fruits, Dairy, Vegetables)'),
            'in_stock_only' => $schema->boolean()->description('Return only products currently in stock'),
            'on_sale' => $schema->boolean()->description('Return only products with active discounts or offer titles'),
            'featured' => $schema->boolean()->description('Return only featured/highlighted products'),
            'limit' => $schema->integer()->description('Maximum number of results to return (1–20, default 8)'),
        ];
    }
}
