<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Chat with AI about meals
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            $validator = $request->validate([
                'question' => ['required', 'string', 'max:1000'],
            ]);

            $question = $validator['question'];
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key is not configured',
                ], 500);
            }

            // Get all meals with their details
            $meals = Meal::with(['category', 'subcategory'])
                ->available()
                ->get()
                ->map(function ($meal) {
                    return [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'description' => $meal->description,
                        'price' => $meal->price,
                        'discount_price' => $meal->discount_price,
                        'final_price' => $meal->final_price,
                        'rating' => $meal->rating,
                        'rating_count' => $meal->rating_count,
                        'size' => $meal->size,
                        'brand' => $meal->brand,
                        'category' => $meal->category->name ?? null,
                        'subcategory' => $meal->subcategory->name ?? null,
                        'is_featured' => $meal->is_featured,
                        'stock_quantity' => $meal->stock_quantity,
                        'in_stock' => $meal->isInStock(),
                        'offer_title' => $meal->offer_title,
                        'has_offer' => $meal->hasOffer(),
                    ];
                });

            // Prepare the prompt with meals data
            $mealsJson = json_encode($meals, JSON_PRETTY_PRINT);
            
            $prompt = "You are a helpful assistant for a grocery/meal delivery app. Here is the current menu of available meals:\n\n" .
                     $mealsJson . "\n\n" .
                     "User Question: " . $question . "\n\n" .
                     "Please provide a helpful answer based on the available meals. " .
                     "You can help users find meals by category, price, rating, or other criteria. " .
                     "Be friendly, concise, and accurate. If the question is not related to meals, politely redirect the conversation.";

            // Call Gemini API
            $response = Http::timeout(30)->post(
                'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key=' . $apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get response from AI',
                    'error' => $response->status() === 401 ? 'Invalid API key' : 'API request failed',
                ], 500);
            }

            $responseData = $response->json();

            // Extract the text response from Gemini
            $aiResponse = null;
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
            }

            if (!$aiResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from AI',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat response generated successfully',
                'data' => [
                    'question' => $question,
                    'answer' => trim($aiResponse),
                    'meals_count' => $meals->count(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
}
