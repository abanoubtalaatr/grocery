<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CallingInvoiceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private OrderItem $orderItem)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('invoices')->insert([
            'order_item_id' => $this->orderItem->id,
            'user_id'       => $this->orderItem->user_id,
            'amount'        => $this->orderItem->quantity * $this->orderItem->product->price,
        ]);
    }
}
