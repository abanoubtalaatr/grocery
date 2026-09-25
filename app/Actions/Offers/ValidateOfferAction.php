<?php

namespace App\Actions\Offers;

use App\Models\Offer;

class ValidateOfferAction
{
    /**
     * Execute the action to validate offer code and calculate discount.
     *
     * @return array{is_usable: bool, offer: Offer|null, discount: float, message: string, not_found: bool}
     */
    public function execute(string $code, ?float $amount = null): array
    {
        $offer = Offer::where('code', $code)->first();

        if (! $offer) {
            return [
                'not_found' => true,
                'is_usable' => false,
                'offer'     => null,
                'discount'  => 0.0,
                'message'   => 'Invalid offer code',
            ];
        }

        $isValid = $offer->isValid();
        $canApply = true;
        $message = 'Offer is valid';

        if ($isValid && $amount !== null) {
            $canApply = $offer->canApplyToAmount($amount);
            if (! $canApply) {
                $message = 'Minimum purchase required: $' . $offer->minimum_purchase;
            }
        }

        $isUsable = $isValid && $canApply;
        $discount = $isUsable ? $offer->calculateDiscount($amount ?? 0) : 0.0;

        return [
            'not_found' => false,
            'is_usable' => $isUsable,
            'offer'     => $offer,
            'discount'  => (float) $discount,
            'message'   => $message,
        ];
    }
}