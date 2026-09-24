<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class AddressController extends Controller
{
    use ApiResponse;
 
    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $addresses = $user->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();

        return $this->success($addresses);
    }

    /**
     * Get single address
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorize('show', $address);

        return $this->success($addresses);
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    { 
        $addresss = $action->handle($request->validated());

        return $this->success($addresses);
    }

    /**
     * Update address
     */
    public function update(Request $request, Address $address, UpdateAddressAction $action): JsonResponse
    {
        $address = $action->handle($request->validated());

        return $this->success($addresses);
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorize('', $address);
        $address->delete();

       return $this->success($addresses);   
    }

}
