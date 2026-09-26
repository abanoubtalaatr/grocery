<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderInvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

 
    public int $timeout = 30;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
   
        $this->order->loadMissing(['user', 'items.meal', 'address']);

        if (!$this->order->user?->email) {
            Log::warning("Invoice Job Skipped: User email is missing for Order ID #{$this->order->id}");
            return;
        }

   
        Mail::to($this->order->user->email)->send(new OrderInvoiceMail($this->order));
    }

    
    public function failed(Throwable $exception): void
    {
        Log::error("SendOrderInvoiceJob failed permanently for Order #{$this->order->id}. Error: " . $exception->getMessage());
    }
}