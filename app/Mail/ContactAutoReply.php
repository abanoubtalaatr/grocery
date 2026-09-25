<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public $contactMessage;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function build()
    {
        return $this->subject('We received your message')
                    ->markdown('emails.contact.auto-reply')
                    ->with([
                        'message' => $this->contactMessage,
                        'companyName' => config('app.name'),
                        'supportEmail' => config('mail.support_email', 'support@example.com'),
                        'phone' => config('app.phone', '+1 (555) 123-4567')
                    ]);
    }
}