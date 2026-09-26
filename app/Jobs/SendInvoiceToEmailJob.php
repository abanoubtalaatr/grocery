<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInvoiceToEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $invoice
    ) {
        //
    }

    public function handle(): void
    {
        Mail::to($this->invoice['customer']['email'])
            ->send(new InvoiceMail($this->invoice));
    }
}