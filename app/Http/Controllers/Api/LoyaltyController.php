<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService,
    ) {}

    /**
     * Loyalty & rewards summary for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Loyalty data retrieved successfully',
                'data' => $this->loyaltyService->buildSummary($request->user()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve loyalty data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
