<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\OrderItem;

class SendEmailJob implements ShouldQueue
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
        $this->orderItem->product->decrement('stock_quantity', $this->orderItem->quantity);
    }
}
