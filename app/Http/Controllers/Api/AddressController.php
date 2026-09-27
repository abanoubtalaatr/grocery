<?php

namespace App\Http\Controllers\Api;

use App\Actions\Address\StoreAddressAction;
use App\Actions\Address\UpdateAddressAction;
use App\Filament\Resources\AddressResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Address\StoreAddressRequest;
use App\Http\Requests\Api\Address\UpdateAddressRequest;
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

        return $this->success(AddressResource::collection($addresses),'successfully');
    }

    /**
     * Get single address
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorize('show', $address);

        return $this->success($address);
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    { 
        $addresss = $action->handle($request,$request);
               return $this->success(AddressResource::collection($addresss),'successfully');


       
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request , UpdateAddressAction $action): JsonResponse
    {
        $address = $action->excute($request,$request);

        return $this->success(AddressResource::collection($address),'successfully');
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
             $this->authorize('delete', $address);
             $address->delete();
           return $this->success($address,'successfully delete');


    
    }

}
