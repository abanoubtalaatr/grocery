<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Http\Requests\UpdateContactMessageRequest;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Submit a contact message.
     */
    public function submit(ContactMessageRequest $request): JsonResponse
    {
        if ($this->isSpam($request->message)) {
            return response()->json([
                'message' => 'Your message appears to be spam',
            ], 400);
        }

        $contactMessage = ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($request->email)
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error(
                'Failed to send contact email: ' . $e->getMessage()
            );
        }

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
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);

        return new ContactMessageCollection(
            $query->paginate($perPage)
        );
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
    public function updateStatus(
        UpdateContactMessageRequest $request,
        ContactMessage $contactMessage
    ): JsonResponse {
        $this->authorize('update', $contactMessage);

        $contactMessage->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

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
            'viagra',
            'casino',
            'loan',
            'debt',
            'free money',
            'work from home',
            'make money fast',
            'click here',
        ];

        $message = strtolower($message);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }
}