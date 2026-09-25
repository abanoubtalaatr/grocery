<?php

namespace App\Http\Controllers\Api;

use App\Actions\Support\CreateSupportReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSupportReportRequest;
use App\Http\Resources\Api\SupportReportResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class SupportController extends Controller
{
    use ResponseTrait;

    /**
     * Submit a support / problem report for the authenticated user.
     */
    public function store(StoreSupportReportRequest $request, CreateSupportReportAction $action): JsonResponse
    {
        $report = $action->execute(
            user: $request->user(),
            data: $request->validated(),
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        return $this->successResponse(
            new SupportReportResource($report),
            'Support report submitted successfully',
            201
        );
    }
}