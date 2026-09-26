<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $invoice
    ) {
        //
    }

    public function build()
    {
        return $this
            ->subject('Your Order Invoice')
            ->view('emails.invoice');
    }
}
