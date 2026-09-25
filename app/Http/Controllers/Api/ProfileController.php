<?php

namespace App\Http\Controllers\Api;

use App\Actions\Profile\DeleteProfileImageAction;
use App\Actions\Profile\GetFullProfileAction;
use App\Actions\Profile\UpdateProfileImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateProfileImageRequest;
use App\Http\Requests\Api\Profile\UpdateProfileInfoRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Http\Resources\Api\SessionResource;
use App\Http\Resources\Api\UserResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ResponseTrait;

    public function show(Request $request, GetFullProfileAction $action): JsonResponse
    {
        $profileData = $action->execute($request->user());

        return $this->successResponse(
            new ProfileResource($profileData),
            'Profile retrieved successfully'
        );
    }

    public function updateImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), $request->file('image'));

        return $this->successResponse(
            new UserResource($user),
            'Profile image updated successfully'
        );
    }

    public function updateInfo(UpdateProfileInfoRequest $request): JsonResponse
    {
        $data = $request->getSanitizedData();

        if (empty($data)) {
            return $this->errorResponse('No data provided to update', 400);
        }

        $user = $request->user();
        $user->update($data);

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    public function deleteImage(Request $request, DeleteProfileImageAction $action): JsonResponse
    {
        $deleted = $action->execute($request->user());

        if (!$deleted) {
            return $this->errorResponse('No profile image to delete', 404);
        }

        return $this->successResponse(null, 'Profile image deleted successfully');
    }

    public function sessions(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get();

        return $this->successResponse(
            SessionResource::collection($tokens),
            'Sessions retrieved successfully'
        );
    }

    public function destroySession(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;

        if ((string) $tokenId === (string) $currentTokenId) {
            return $this->errorResponse('Cannot revoke your current session from this request. Use logout instead.', 400);
        }

        $token = $user->tokens()->find($tokenId);
        if (!$token) {
            return $this->errorResponse('Session not found', 404);
        }

        $token->delete();

        return $this->successResponse(null, 'Session revoked successfully');
    }
}