<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendChatMessageRequest;
use App\Http\Resources\Chatbot\ChatbotMessageResource;
use App\Services\ChatbotService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly ChatbotService $chatbotService) {}

    /**
     * Send a message to the AI assistant.
     */
    public function chat(SendChatMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $conversationId = $validated['conversation_id'] ?? $validated['session_id'] ?? null;

        $result = $this->chatbotService->chat(
            user: $request->user(),
            question: trim($validated['question']),
            conversationId: $conversationId,
            locale: $validated['locale'] ?? null,
            rating: $validated['rating'] ?? null
        );

        return $this->successResponse(
            new ChatbotMessageResource($result),
            'Chat response generated successfully'
        );
    }

    /**
     * Get current user's chatbot conversation history (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        $messages = $request->user()
            ->chatbotMessages()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse([
            'items' => ChatbotMessageResource::collection($messages->items()),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
                'from'         => $messages->firstItem(),
                'to'           => $messages->lastItem(),
            ],
        ], 'Chat history retrieved successfully');
    }

    /**
     * Get suggested quick-reply questions (localised).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $isAr = $request->input('locale', 'en') === 'ar';

        $suggestions = $isAr
            ? [
                ['id' => 'faq',      'label' => 'أسئلة شائعة',        'question' => 'ما هي الأسئلة الشائعة؟'],
                ['id' => 'orders',   'label' => 'تتبع الطلب',          'question' => 'كيف أتتبع طلبي؟'],
                ['id' => 'payment',  'label' => 'طرق الدفع',           'question' => 'ما طرق الدفع المتاحة؟'],
                ['id' => 'products', 'label' => 'المنتجات والمفضلة',   'question' => 'ما المنتجات المتاحة والعروض؟'],
                ['id' => 'offers',   'label' => 'كوبونات وعروض',       'question' => 'ما العروض وكوبونات الخصم الحالية؟'],
            ]
            : [
                ['id' => 'faq',      'label' => 'FAQs',              'question' => 'What are the frequently asked questions?'],
                ['id' => 'orders',   'label' => 'Track order',        'question' => 'How do I track my order?'],
                ['id' => 'payment',  'label' => 'Payment methods',    'question' => 'What payment methods do you accept?'],
                ['id' => 'products', 'label' => 'Products & offers',  'question' => 'What products and offers do you have?'],
                ['id' => 'offers',   'label' => 'Coupons & offers',   'question' => 'What promo codes or offers are available?'],
            ];

        return $this->successResponse(
            ['suggestions' => $suggestions],
            'Suggestions retrieved successfully'
        );
    }
}