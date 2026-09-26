<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateInvoiceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId
    ) {
        //
    }

    public function handle(InvoiceService $invoiceService): void
    {
        $order = Order::with([
            'user',
            'address',
            'items.meal.category',
            'items.meal.subcategory',
        ])->findOrFail($this->orderId);

        $invoice = $invoiceService->formatReceipt($order);

        SendInvoiceToEmailJob::dispatch($invoice);
    }
}