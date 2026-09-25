<?php

namespace App\Http\Controllers\Api\Address;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AddressResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $user->addresses()
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => new AddressResource($address->fresh()),
        ]);
    }
}