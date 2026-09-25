<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\EmailValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Submit a contact message.
     */
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', ...EmailValidation::formatRules(), 'max:255'],
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:250',
        ], [
            'email.not_regex' => EmailValidation::trailingHyphenDotBeforeAtMessage(),
            'email.regex' => EmailValidation::domainStructureMessage(),
            'email.max' => 'The email address may not exceed 255 characters.',
        ]);

        if ($this->isSpam($data['message'])) {
            return response()->json([
                'message' => 'Your message appears to be spam',
            ], 400);
        }

        $contactMessage = ContactMessage::create([
            ...$data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->sendNotifications($contactMessage);

        return response()->json([
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => new ContactMessageResource($contactMessage),
        ], 201);
    }

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request): ContactMessageCollection
    {
        $this->authorize('viewAny', ContactMessage::class);

        $query = ContactMessage::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSortFields = ['created_at', 'name', 'email', 'status'];
        $sortBy = in_array($sortBy, $allowedSortFields, true) ? $sortBy : 'created_at';

        $sortOrder = strtolower($request->input('sort_order', 'desc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $messages = $query->paginate($perPage);

        return new ContactMessageCollection($messages);
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage): ContactMessageResource
    {
        $this->authorize('view', $contactMessage);

        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return new ContactMessageResource($contactMessage);
    }

    /**
     * Update contact message status (admin only).
     */
    public function updateStatus(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('update', $contactMessage);

        $data = $request->validate([
            'status' => 'required|in:read,replied,spam',
            'admin_notes' => 'nullable|string',
        ]);

        $contactMessage->update($data);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    /**
     * Get contact statistics (admin only).
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $total = ContactMessage::count();
        $new = ContactMessage::new()->count();
        $read = ContactMessage::read()->count();
        $replied = ContactMessage::replied()->count();
        $spam = ContactMessage::spam()->count();

        // Monthly statistics for the last 6 months
        $monthlyStats = ContactMessage::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as total,
            SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN status = "replied" THEN 1 ELSE 0 END) as replied
        ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'data' => [
                'total' => $total,
                'new' => $new,
                'read' => $read,
                'replied' => $replied,
                'spam' => $spam,
                'monthly_stats' => $monthlyStats,
            ],
        ]);
    }

    /**
     * Simple spam detection.
     */
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

    private function sendNotifications(ContactMessage $contactMessage): void
    {
        Mail::to(config('mail.admin_email', 'admin@example.com'))
            ->send(new ContactMessageReceived($contactMessage));

        Mail::to($contactMessage->email)
            ->send(new ContactAutoReply($contactMessage));
    }
}
