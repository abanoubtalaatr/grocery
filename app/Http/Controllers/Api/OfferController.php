<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;

class OfferController extends Controller
{
    // Get all active offers
    public function index(Request $request)
    {
        $query = Offer::active();
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by minimum purchase
        if ($request->has('min_purchase')) {
            $query->where('minimum_purchase', '<=', $request->min_purchase)
                  ->orWhereNull('minimum_purchase');
        }
        
        // Featured offers only
        if ($request->boolean('featured')) {
            $query->featured();
        }
        
        // Search by title or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $offers = $query->paginate($perPage);
        
        return OfferResource::collection($offers);
    }

    // Get featured offers
    public function featured()
    {
        $offers = Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return OfferResource::collection($offers);
    }

    // Get offer by code
    public function showByCode($code)
    {
        $offer = Offer::where('code', $code)->firstOrFail();
        
        return new OfferResource($offer);
    }

    // Validate offer code
    public function validateOffer(Request $request)
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