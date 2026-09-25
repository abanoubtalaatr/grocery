<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendChatMessageRequest;
use App\Actions\Chatbot\SendChatMessageAction;
use App\Actions\Chatbot\GetChatHistoryAction;
use App\Actions\Chatbot\GetChatSuggestionsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Send a message to the AI assistant.
     */
    public function chat(SendChatMessageRequest $request, SendChatMessageAction $action): JsonResponse
    {
        $result = $action->handle($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Chat response generated successfully',
            'data' => [
                'id' => $result['id'],
                'conversation_id' => $result['conversation_id'],
                'session_id' => $result['conversation_id'], // backwards compatibility
                'question' => $result['question'],
                'answer' => $result['answer'],
                'rating' => $result['rating'],
            ],
        ]);
    }

    /**
     * Get current user's chatbot conversation history (paginated).
     */
    public function history(Request $request, GetChatHistoryAction $action): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        $result = $action->handle($request->user(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Chat history retrieved successfully',
            'data' => [
                'items' => $result['items'],
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    /**
     * Get suggested quick-reply questions (localised).
     */
    public function suggestions(Request $request, GetChatSuggestionsAction $action): JsonResponse
    {
        $locale = $request->input('locale', 'en');
        $suggestions = $action->handle($locale);

        return response()->json([
            'success' => true,
            'message' => 'Suggestions retrieved successfully',
            'data' => ['suggestions' => $suggestions],
        ]);
    }
}