<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Http\Resources\ContactMessageCollection;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageReceived;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Submit a contact message.
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            // 'g-recaptcha-response' => 'required|recaptcha' // If using reCAPTCHA
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for spam (simple check for demo)
        if ($this->isSpam($request->message, $request->email)) {
            return response()->json([
                'message' => 'Your message appears to be spam'
            ], 400);
        }

        // Create contact message
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
            // Send notification email to admin
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            // Send auto-reply to user
            Mail::to($request->email)
                ->send(new \App\Mail\ContactAutoReply($contactMessage));

        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => new ContactMessageResource($contactMessage)
        ], 201);
    }

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ContactMessage::class);

        $query = ContactMessage::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $messages = $query->paginate($perPage);

        return new ContactMessageCollection($messages);
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage)
    {
        $this->authorize('view', $contactMessage);

        // Mark as read when viewing
        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return new ContactMessageResource($contactMessage);
    }

    /**
     * Update contact message status (admin only).
     */
    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $this->authorize('update', $contactMessage);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:read,replied,spam',
            'admin_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $contactMessage->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new ContactMessageResource($contactMessage)
        ]);
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return response()->json([
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Get contact statistics (admin only).
     */
    public function statistics()
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
                'monthly_stats' => $monthlyStats
            ]
        ]);
    }

    /**
     * Simple spam detection.
     */
    private function isSpam($message, $email): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'loan', 'debt', 'free money',
            'work from home', 'make money fast', 'click here'
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