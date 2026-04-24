<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use App\Models\Faq;
use App\Models\Meal;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotController extends Controller
{
    /**
     * Chat with AI (meals, FAQs, orders, payment, offers). Saves history and optional rating.
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            foreach (['question', 'message'] as $key) {
                if ($request->hasFile($key)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Send the question as plain text (form field or JSON), not as a file upload.',
                        'errors' => ['question' => ['The question must be a text value, not a file.']],
                    ], 422);
                }
            }

            if ($request->filled('message') && ! $request->filled('question')) {
                $request->merge(['question' => $request->input('message')]);
            }

            $rawQuestion = $request->input('question');
            if (is_array($rawQuestion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Send a single question text only.',
                    'errors' => ['question' => ['Multiple question values are not allowed.']],
                ], 422);
            }

            $validator = $request->validate([
                'question' => ['required', 'string', 'max:1000'],
                'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
                'locale' => ['nullable', 'string', 'in:ar,en'],
            ]);

            $question = trim((string) $validator['question']);
            if ($question === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['question' => ['A non-empty question is required.']],
                ], 422);
            }
            $rating = $validator['rating'] ?? null;
            $locale = $validator['locale'] ?? null;
            $apiKey = env('GEMINI_API_KEY');

            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key is not configured',
                ], 503);
            }

            $user = $request->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }

            $meals = Meal::with(['category', 'subcategory'])
                ->available()
                ->orderByDesc('sold_count')
                ->limit(250)
                ->get()
                ->map(function ($meal) {
                    return [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'description' => $meal->description,
                        ...$meal->getApiPriceAttributes(),
                        'rating' => (float) $meal->rating,
                        'rating_count' => (int) $meal->rating_count,
                        'size' => $meal->size,
                        'brand' => $meal->brand,
                        'category' => $meal->category?->name,
                        'subcategory' => $meal->subcategory?->name,
                        'is_featured' => $meal->is_featured,
                        'stock_quantity' => $meal->stock_quantity,
                        'in_stock' => $meal->isInStock(),
                        'offer_title' => $meal->offer_title,
                        'has_offer' => $meal->hasOffer(),
                    ];
                });

            $faqs = Faq::active()->ordered()->get(['question', 'answer', 'category'])->toArray();
            $offers = Offer::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->get(['title', 'code', 'description', 'type', 'discount_value', 'minimum_purchase'])
                ->toArray();

            $localeHint = $this->getLocaleHint($locale);

            $prompt = 'You are a helpful assistant for a grocery/meal delivery app. '.$localeHint."\n\n";

            $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
            $prompt .= "## Available meals (menu)\n".json_encode($meals, $jsonFlags)."\n\n";

            if (! empty($faqs)) {
                $prompt .= "## FAQ (use these to answer general questions)\n".json_encode($faqs, $jsonFlags)."\n\n";
            }
            if (! empty($offers)) {
                $prompt .= "## Active offers / promo codes\n".json_encode($offers, $jsonFlags)."\n\n";
            }

            $prompt .= "## Guidelines\n";
            $prompt .= "- For order status / track order: explain that the user can go to 'My Orders' or 'Track Order' in the app to see status. Do not invent order IDs.\n";
            $prompt .= "- For payment: we support card and cash on delivery; guide them to checkout or payment settings.\n";
            $prompt .= "- For products, favorites, smart lists: use the meals data above; you can suggest categories or featured items.\n";
            $prompt .= "- For coupons: use the active offers list; mention code and conditions if relevant.\n";
            $prompt .= 'User question: '.$question."\n\n";
            $prompt .= 'Provide a helpful, concise answer. If the question is off-topic, politely redirect to app features (orders, meals, offers, FAQ).';

            $response = Http::timeout(30)->post(
                'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key='.$apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get response from AI',
                    'error' => $response->status() === 401 ? 'Invalid API key' : 'API request failed',
                ], 502);
            }

            $responseData = $response->json() ?? [];
            $aiResponse = $this->extractGeminiText($responseData);

            if ($aiResponse === null || $aiResponse === '') {
                Log::warning('Gemini returned no text candidate', ['body' => $response->body()]);

                return response()->json([
                    'success' => false,
                    'message' => 'No response from AI',
                    'error' => $responseData['promptFeedback']['blockReason'] ?? 'empty_or_blocked',
                ], 502);
            }

            $answer = trim($aiResponse);

            $message = $user->chatbotMessages()->create([
                'question' => $question,
                'answer' => $answer,
                'rating' => $rating,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat response generated successfully',
                'data' => [
                    'id' => $message->id,
                    'question' => $question,
                    'answer' => $answer,
                    'rating' => $message->rating,
                    'meals_count' => $meals->count(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
     * @param  array<string, mixed>  $responseData
     */
    private function extractGeminiText(array $responseData): ?string
    {
        $candidates = $responseData['candidates'] ?? [];
        if (! is_array($candidates)) {
            return null;
        }

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }
            $parts = $candidate['content']['parts'] ?? null;
            if (! is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (is_array($part) && isset($part['text']) && is_string($part['text']) && $part['text'] !== '') {
                    return $part['text'];
                }
            }
        }

        return null;
    }

    private function getLocaleHint(?string $locale): string
    {
        if ($locale === 'ar') {
            return 'Respond in Arabic (العربية) unless the user wrote in English.';
        }
        if ($locale === 'en') {
            return 'Respond in English.';
        }

        return 'Respond in the same language the user used.';
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

            $items = $messages->getCollection()->map(function (ChatbotMessage $m) {
                return [
                    'id' => $m->id,
                    'question' => $m->question,
                    'answer' => $m->answer,
                    'rating' => $m->rating,
                    'created_at' => $m->created_at,
                ];
            });

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
     * Get suggested questions for the chatbot (quick replies).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $locale = $request->input('locale', 'en');
        $isAr = $locale === 'ar';

        $suggestions = $isAr
            ? [
                ['id' => 'faq', 'label' => 'أسئلة شائعة', 'question' => 'ما هي الأسئلة الشائعة؟'],
                ['id' => 'orders', 'label' => 'تتبع الطلب', 'question' => 'كيف أتتبع طلبي؟'],
                ['id' => 'payment', 'label' => 'طرق الدفع', 'question' => 'ما طرق الدفع المتاحة؟'],
                ['id' => 'products', 'label' => 'المنتجات والمفضلة', 'question' => 'ما المنتجات المتاحة والعروض؟'],
                ['id' => 'offers', 'label' => 'كوبونات وعروض', 'question' => 'ما العروض وكوبونات الخصم الحالية؟'],
            ]
            : [
                ['id' => 'faq', 'label' => 'FAQs', 'question' => 'What are the frequently asked questions?'],
                ['id' => 'orders', 'label' => 'Track order', 'question' => 'How do I track my order?'],
                ['id' => 'payment', 'label' => 'Payment methods', 'question' => 'What payment methods do you accept?'],
                ['id' => 'products', 'label' => 'Products & favorites', 'question' => 'What products and offers do you have?'],
                ['id' => 'offers', 'label' => 'Coupons & offers', 'question' => 'What promo codes or offers are available?'],
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
