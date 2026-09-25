<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbotService) {}

    /**
     * Send a message to the AI assistant.
     *
     * Supports an optional `session_id` (UUID) to maintain conversation history across requests.
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            foreach (['question', 'message'] as $key) {
                if ($request->hasFile($key)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Send the question as plain text, not as a file upload.',
                        'errors' => ['question' => ['The question must be a text value, not a file.']],
                    ], 422);
                }
            }

            if ($request->filled('message') && ! $request->filled('question')) {
                $request->merge(['question' => $request->input('message')]);
            }

            if (is_array($request->input('question'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Send a single question text only.',
                    'errors' => ['question' => ['Multiple question values are not allowed.']],
                ], 422);
            }

            $validated = $request->validate([
                'question' => ['required', 'string', 'max:1000'],
                'conversation_id' => ['nullable', 'uuid'],
                'session_id' => ['nullable', 'uuid'],       // kept for backwards compatibility
                'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
                'locale' => ['nullable', 'string', 'in:ar,en'],
            ]);

            $question = trim((string) $validated['question']);

            if ($question === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['question' => ['A non-empty question is required.']],
                ], 422);
            }

            $user = $request->user();
            if ($user === null) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $conversationId = $validated['conversation_id'] ?? $validated['session_id'] ?? null;

            $result = $this->chatbotService->chat(
                user: $user,
                question: $question,
                conversationId: $conversationId,
                locale: $validated['locale'] ?? null,
            );

            if (isset($validated['rating'])) {
                ChatbotMessage::where('id', $result['id'])->update(['rating' => $validated['rating']]);
                $result['rating'] = $validated['rating'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat response generated successfully',
                'data' => [
                    'id' => $result['id'],
                    'conversation_id' => $result['conversation_id'],
                    'session_id' => $result['conversation_id'],  // backwards compatibility
                    'question' => $result['question'],
                    'answer' => $result['answer'],
                    'rating' => $result['rating'],
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Chatbot Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process chat request',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get current user's chatbot conversation history (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
            $messages = $request->user()
                ->chatbotMessages()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $items = $messages->getCollection()->map(fn (ChatbotMessage $m) => [
                'id' => $m->id,
                'session_id' => $m->session_id,
                'question' => $m->question,
                'answer' => $m->answer,
                'rating' => $m->rating,
                'created_at' => $m->created_at,
            ]);

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
        } catch (\Exception $e) {
            Log::error('Chatbot history error', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chat history',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get suggested quick-reply questions (localised).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $locale = $request->input('locale', 'en');
        $isAr = $locale === 'ar';

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

        return response()->json([
            'success' => true,
            'message' => 'Suggestions retrieved successfully',
            'data' => ['suggestions' => $suggestions],
        ]);
    }
}
