<?php

namespace App\Jobs;

use App\Mail\OrderInvoice;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        $this->order->load([
            'user',
            'address',
            'items.meal',
        ]);

        Mail::to($this->order->user->email)
            ->send(new OrderInvoice($this->order));
    }
}
