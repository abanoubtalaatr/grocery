<?php

namespace App\Ai\Tools;

use App\Models\Offer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CheckOffersTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get all currently active promo codes and discount offers. Use this whenever the user asks about coupons, discounts, promo codes, deals, or special offers.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $offers = Offer::active()->get([
            'title', 'code', 'description', 'type', 'discount_value', 'minimum_purchase',
        ]);

        if ($offers->isEmpty()) {
            return json_encode(['found' => false, 'message' => 'There are no active promo codes or offers at this time.'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'found' => true,
            'count' => $offers->count(),
            'offers' => $offers->map(fn ($offer) => [
                'title' => $offer->title,
                'code' => $offer->code,
                'description' => $offer->description,
                'type' => $offer->type,
                'discount' => (float) $offer->discount_value,
                'minimum_purchase' => $offer->minimum_purchase !== null ? (float) $offer->minimum_purchase : null,
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
