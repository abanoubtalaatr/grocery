<x-mail::message>
# Thank You for Contacting Us

Dear {{ $message->name }},

We have received your message and appreciate you taking the time to reach out to us.

**Your Message Details:**
- Subject: {{ $message->subject }}
- Date: {{ $message->created_at->format('F j, Y g:i A') }}

Our team will review your inquiry and get back to you within 24-48 hours during business hours.

**Reference Number:** CONTACT-{{ str_pad($message->id, 6, '0', STR_PAD_LEFT) }}

If you need immediate assistance, please contact us at:
- Email: {{ $supportEmail }}
- Phone: {{ $phone }}

Best regards,<br>
The {{ $companyName }} Team
</x-mail::message>