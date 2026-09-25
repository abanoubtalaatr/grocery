<x-mail::message>
# New Contact Message Received

**From:** {{ $message->name }}<br>
**Email:** {{ $message->email }}<br>
**Phone:** {{ $message->phone ?? 'Not provided' }}<br>
**Date:** {{ $message->created_at->format('F j, Y g:i A') }}

**Subject:** {{ $message->subject }}

**Message:**
{{ $message->message }}

<x-mail::button :url="$url">
View Message in Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>