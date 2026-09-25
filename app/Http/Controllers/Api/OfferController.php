<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    // Get all active offers
    public function index(Request $request): JsonResponse
    {
        $query = Offer::active();
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }
        
        // Filter by minimum purchase
        if ($request->has('min_purchase')) {
            $minimumPurchase = $request->input('min_purchase');
            $query->where(function ($query) use ($minimumPurchase) {
                $query->where('minimum_purchase', '<=', $minimumPurchase)
                    ->orWhereNull('minimum_purchase');
            });
        }
        
        // Featured offers only
        if ($request->boolean('featured')) {
            $query->featured();
        }
        
        // Search by title or code
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Order by
        $orderBy = $request->input('order_by', 'created_at');
        $orderBy = in_array($orderBy, ['created_at', 'title', 'minimum_purchase'], true)
            ? $orderBy : 'created_at';
        $orderDirection = strtolower($request->input('order_direction', 'desc'));
        $orderDirection = in_array($orderDirection, ['asc', 'desc'], true) ? $orderDirection : 'desc';
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $offers = $query->paginate($perPage);
        
        return OfferResource::collection($offers);
    }

    // Get featured offers
    public function featured(): AnonymousResourceCollection
    {
        $offers = Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return OfferResource::collection($offers);
    }

    // Get offer by code
    public function showByCode(string $code): OfferResource
    {
        $offer = Offer::where('code', $code)->firstOrFail();
        
        return new OfferResource($offer);
    }

    // Validate offer code
    public function validateOffer(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
        ]);
        
        $offer = Offer::where('code', $request->code)->first();
        
        if (!$offer) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid offer code',
            ], 404);
        }
        
        $isValid = $offer->isValid();
        $canApply = true;
        $message = 'Offer is valid';
        
        if ($isValid && $request->has('amount')) {
            $canApply = $offer->canApplyToAmount($request->amount);
            if (!$canApply) {
                $message = 'Minimum purchase required: $' . $offer->minimum_purchase;
            }
        }
        
        $discount = $canApply && $isValid 
            ? $offer->calculateDiscount($request->amount ?? 0)
            : 0;
        
        return response()->json([
            'valid' => $isValid && $canApply,
            'offer' => new OfferResource($offer),
            'discount_amount' => $discount,
            'message' => $message,
        ]);
    }
}