<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $addresses = $user->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($address) {
                    return $this->formatAddress($address);
                });

            return response()->json([
                'success' => true,
                'message' => 'Addresses retrieved successfully',
                'data' => $addresses,
                'total_count' => $addresses->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single address
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Address retrieved successfully',
                'data' => $this->formatAddress($address),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new address
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'label' => ['nullable', 'string', 'max:255'],
                'full_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[1-9]\d{1,14}$/'],
                'country_code' => ['nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'street_address' => ['required', 'string', 'max:500'],
                'building_number' => ['nullable', 'string', 'max:50'],
                'floor' => ['nullable', 'string', 'max:50'],
                'apartment' => ['nullable', 'string', 'max:50'],
                'landmark' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'postal_code' => ['nullable', 'string', 'max:20'],
                'country' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'is_default' => ['nullable', 'boolean'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            DB::beginTransaction();

            // If this is the first address, make it default
            $isFirstAddress = $user->addresses()->count() === 0;
            
            $address = $user->addresses()->create(array_merge(
                $validator->validated(),
                ['is_default' => $request->boolean('is_default') || $isFirstAddress]
            ));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => $this->formatAddress($address),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update address
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'label' => ['sometimes', 'string', 'max:255'],
                'full_name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['sometimes', 'string', 'max:20', 'regex:/^\+?[1-9]\d{1,14}$/'],
                'country_code' => ['sometimes', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'street_address' => ['sometimes', 'string', 'max:500'],
                'building_number' => ['nullable', 'string', 'max:50'],
                'floor' => ['nullable', 'string', 'max:50'],
                'apartment' => ['nullable', 'string', 'max:50'],
                'landmark' => ['nullable', 'string', 'max:255'],
                'city' => ['sometimes', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'postal_code' => ['nullable', 'string', 'max:20'],
                'country' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'is_default' => ['nullable', 'boolean'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $address->update($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $this->formatAddress($address->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            DB::beginTransaction();

            $wasDefault = $address->is_default;
            $address->delete();

            // If deleted address was default, set another address as default
            if ($wasDefault) {
                $newDefault = $user->addresses()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set address as default
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            DB::beginTransaction();

            $address->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully',
                'data' => $this->formatAddress($address->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format address data for response
     */
    private function formatAddress(Address $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'country_code' => $address->country_code,
            'formatted_phone' => $address->formatted_phone,
            'street_address' => $address->street_address,
            'building_number' => $address->building_number,
            'floor' => $address->floor,
            'apartment' => $address->apartment,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'notes' => $address->notes,
            'is_default' => $address->is_default,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'full_address' => $address->full_address,
            'created_at' => $address->created_at,
            'updated_at' => $address->updated_at,
        ];
    }
}
