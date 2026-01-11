<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $contactMessage;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function build()
    {
        return $this->subject('New Contact Message: ' . $this->contactMessage->subject)
                    ->markdown('emails.contact.received')
                    ->with([
                        'message' => $this->contactMessage,
                        'url' => url('/admin/contact-messages/' . $this->contactMessage->id)
                    ]);
    }
}