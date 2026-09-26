<?php

namespace App\Jobs;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $order = Order::with(['user', 'items.meal', 'address'])->findOrFail($this->orderId);

        if (! $order->user?->email) {
            return;
        }

        Mail::to($order->user->email)->send(new OrderInvoiceMail($order));
    }
}
