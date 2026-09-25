<?php
namespace App\Http\Controllers\Api;

use App\Actions\Contact\GetContactMessagesAction;
use App\Actions\Contact\GetContactStatisticsAction;
use App\Actions\Contact\SubmitContactMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\UpdateContactStatusRequest;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ResponseTrait;

    /**
     * Submit a contact message.
     */
    public function submit(StoreContactMessageRequest $request, SubmitContactMessageAction $action): JsonResponse
    {
        $contactMessage = $action->execute(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'Thank you for your message. We will get back to you soon.',
            201
        );
    }

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request, GetContactMessagesAction $action): ContactMessageCollection
    {
        $this->authorize('viewAny', ContactMessage::class);

        $messages = $action->execute($request);

        return new ContactMessageCollection($messages);
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('view', $contactMessage);

        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'Message retrieved successfully'
        );
    }

    /**
     * Update contact message status (admin only).
     */
    public function updateStatus(UpdateContactStatusRequest $request, ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('update', $contactMessage);

        $contactMessage->update($request->validated());

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'Status updated successfully'
        );
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return $this->successResponse(null, 'Message deleted successfully');
    }

    /**
     * Get contact statistics (admin only).
     */
    public function statistics(GetContactStatisticsAction $action): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        return $this->successResponse(
            $action->execute(),
            'Statistics retrieved successfully'
        );
    }
}