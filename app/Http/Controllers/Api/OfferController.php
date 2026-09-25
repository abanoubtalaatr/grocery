<?php

namespace App\Http\Controllers\Api;

use App\Actions\Offers\GetFilteredOffersAction;
use App\Actions\Offers\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OfferIndexRequest;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    use ResponseTrait;

    /**
     * Get all active offers.
     */
    public function index(OfferIndexRequest $request, GetFilteredOffersAction $action): AnonymousResourceCollection
    {
        $offers = $action->execute($request->validated());

        return OfferResource::collection($offers);
    }

    /**
     * Validate an offer code.
     */
    public function validateOffer(ValidateOfferRequest $request, ValidateOfferAction $action): JsonResponse
    {
        $validated = $request->validated();
        
        $result = $action->execute(
            code: $validated['code'],
            amount: $validated['amount'] ?? null
        );


        return $this->successResponse([
            'valid'           => $result['is_usable'],
            'offer'           => new OfferResource($result['offer']),
            'discount_amount' => $result['discount'],
            'message'         => $result['message'],
        ], 'Offer validation evaluated successfully');
    }
}