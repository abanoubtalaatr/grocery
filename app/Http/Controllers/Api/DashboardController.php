<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Get dashboard statistics and insights.
     */
    public function index(Request $request): JsonResponse
    {
        $dashboardData = $this->dashboardService->getDashboardData($request->user());

        return $this->successResponse(
            new DashboardResource($dashboardData),
            'Dashboard data retrieved successfully'
        );
    }
}