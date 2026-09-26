<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Jobs\CallingInventoryJob;
use App\Jobs\CallingInvoiceJob;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

class OrderItemController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
       $user =$request->user();

         $orderItems = $user->orderItems()->get();
    
          return $this->success($orderItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $orderItem = $request->user()->orderItems()->create($data);

        CallingInventoryJob::dispatch($orderItem);
        CallingInvoiceJob::dispatch($orderItem);

        return $this->success($orderItem, 'Order item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, OrderItem $orderItem): JsonResponse
    {
       $this->authorize('show', $orderItem);
       return $this->success($orderItem, 'Order item fetched successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, OrderItem $orderItem): JsonResponse
    {
        $this->authorize('delete', $orderItem);
        $orderItem->delete();

        return $this->success($orderItem, 'Order item deleted successfully.');
    }
}
