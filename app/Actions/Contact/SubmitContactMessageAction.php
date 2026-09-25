<?php
namespace App\Actions\Contact;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SubmitContactMessageAction
{
    public function execute(array $data, string $ip, ?string $userAgent): ContactMessage
    {
        if ($this->isSpam($data['message'])) {
            throw ValidationException::withMessages([
                'message' => ['Your message appears to be spam.'],
            ]);
        }

        $contactMessage = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        $this->sendNotificationEmails($contactMessage, $data['email']);

        return $contactMessage;
    }

    private function isSpam(string $message): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'loan', 'debt', 'free money',
            'work from home', 'make money fast', 'click here',
        ];

        $message = strtolower($message);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function sendNotificationEmails(ContactMessage $contactMessage, string $userEmail): void
    {
        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($userEmail)
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
        }
    }
}