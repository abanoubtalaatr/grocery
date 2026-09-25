<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Address\StoreAddressAction;
use App\Http\Requests\Api\Address\StoreAddressRequest;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => AddressResource::collection($addresses),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $address = $this->findUserAddress($request, $id);

        return response()->json([
            'success' => true,
            'data' => new AddressResource($address),
        ]);
    }

    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    {
        $user = $request->user();
        $address = $action->handle($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => new AddressResource($address),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $address = $this->findUserAddress($request, $id);
        $data = $request->validate([
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'full_name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'min:10', 'max:20', 'regex:/^\+?[1-9]\d{9,14}$/'],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
            'street_address' => ['sometimes', 'required', 'string', 'min:5', 'max:500'],
            'building_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'floor' => ['sometimes', 'nullable', 'string', 'max:50'],
            'apartment' => ['sometimes', 'nullable', 'string', 'max:50'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ]);

        $address->update($this->normalizePhone($data));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => new AddressResource($address->fresh()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $address = $this->findUserAddress($request, $id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        $address = $this->findUserAddress($request, $id);

        $request->user()->addresses()
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => new AddressResource($address->fresh()),
        ]);
    }

    private function findUserAddress(Request $request, string $id): Address
    {
        return $request->user()->addresses()->findOrFail($id);
    }

    private function normalizePhone(array $data): array
    {
        if (! empty($data['phone']) && ! empty($data['country_code'])) {
            $phone = trim($data['phone']);
            $countryCode = trim($data['country_code']);

            if (str_starts_with($phone, $countryCode)) {
                $data['phone'] = substr($phone, strlen($countryCode));
            }
        }

        return $data;
    }
}
