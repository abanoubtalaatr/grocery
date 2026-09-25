<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotRequest;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbotService
    ) {
    }

    /**
     * Send a message to the AI assistant.
     */
    public function chat(ChatbotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $question = trim($validated['question']);

        $result = $this->chatbotService->chat(
            user: $request->user(),
            question: $question,
            conversationId: $validated['conversation_id']
                ?? $validated['session_id']
                ?? null,
            locale: $validated['locale'] ?? null,
        );

        if (isset($validated['rating'])) {
            ChatbotMessage::where('id', $result['id'])
                ->update([
                    'rating' => $validated['rating'],
                ]);

            $result['rating'] = $validated['rating'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Chat response generated successfully',
            'data' => [
                'id' => $result['id'],
                'conversation_id' => $result['conversation_id'],
                'session_id' => $result['conversation_id'],
                'question' => $result['question'],
                'answer' => $result['answer'],
                'rating' => $result['rating'],
            ],
        ]);
    }

    /**
     * Get current user's chatbot conversation history.
     */
    public function history(Request $request): JsonResponse
    {
        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            50
        );

        $messages = $request->user()
            ->chatbotMessages()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = $messages->getCollection()->map(
            fn (ChatbotMessage $message) => [
                'id' => $message->id,
                'session_id' => $message->session_id,
                'question' => $message->question,
                'answer' => $message->answer,
                'rating' => $message->rating,
                'created_at' => $message->created_at,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Chat history retrieved successfully',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'from' => $messages->firstItem(),
                    'to' => $messages->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Get suggested quick-reply questions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $isAr = $request->input('locale', 'en') === 'ar';

        $suggestions = $isAr
            ? [
                [
                    'id' => 'faq',
                    'label' => 'أسئلة شائعة',
                    'question' => 'ما هي الأسئلة الشائعة؟',
                ],
                [
                    'id' => 'orders',
                    'label' => 'تتبع الطلب',
                    'question' => 'كيف أتتبع طلبي؟',
                ],
                [
                    'id' => 'payment',
                    'label' => 'طرق الدفع',
                    'question' => 'ما طرق الدفع المتاحة؟',
                ],
                [
                    'id' => 'products',
                    'label' => 'المنتجات والمفضلة',
                    'question' => 'ما المنتجات المتاحة والعروض؟',
                ],
                [
                    'id' => 'offers',
                    'label' => 'كوبونات وعروض',
                    'question' => 'ما العروض وكوبونات الخصم الحالية؟',
                ],
            ]
            : [
                [
                    'id' => 'faq',
                    'label' => 'FAQs',
                    'question' => 'What are the frequently asked questions?',
                ],
                [
                    'id' => 'orders',
                    'label' => 'Track order',
                    'question' => 'How do I track my order?',
                ],
                [
                    'id' => 'payment',
                    'label' => 'Payment methods',
                    'question' => 'What payment methods do you accept?',
                ],
                [
                    'id' => 'products',
                    'label' => 'Products & offers',
                    'question' => 'What products and offers do you have?',
                ],
                [
                    'id' => 'offers',
                    'label' => 'Coupons & offers',
                    'question' => 'What promo codes or offers are available?',
                ],
            ];

        return response()->json([
            'success' => true,
            'message' => 'Suggestions retrieved successfully',
            'data' => [
                'suggestions' => $suggestions,
            ],
        ]);
    }
}