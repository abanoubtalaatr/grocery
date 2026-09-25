<?php

namespace App\Services;

use App\Ai\Agents\GroceryAssistant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\RateLimitedException;
use RuntimeException;
use Throwable;

class ChatbotService
{
    /**
     * Process a user chat message and return the AI answer.
     *
     * @return array{id: int, conversation_id: string, question: string, answer: string, rating: int|null}
     *
     * @throws RuntimeException
     */
    public function chat(User $user, string $question, ?string $conversationId, ?string $locale): array
    {
        $providerName = config('ai.default', 'anthropic');
        $model = config('ai.providers.'.$providerName.'.models.text.default', config('ai.providers.'.$providerName.'.model'));
        $provider = Lab::from($providerName);

        if (! config('ai.providers.'.$providerName.'.key')) {
            throw new RuntimeException("AI provider [{$providerName}] API key is not configured.");
        }

        $agent = new GroceryAssistant(locale: $locale);

        try {
            $response = $conversationId
                ? $agent->continue($conversationId, as: $user)->prompt($question, provider: $provider, model: $model, timeout: 60)
                : $agent->forUser($user)->prompt($question, provider: $provider, model: $model, timeout: 60);

            $answer = trim((string) $response);
            $conversationId = $response->conversationId ?? $conversationId;
        } catch (RateLimitedException $e) {
            Log::warning('AI rate limited', ['provider' => $providerName, 'model' => $model]);
            throw new RuntimeException("AI provider [{$providerName}] quota exceeded. Please try again in a moment.", previous: $e);
        } catch (Throwable $e) {
            Log::error('AI provider error', ['provider' => $providerName, 'message' => $e->getMessage()]);
            throw new RuntimeException('AI request failed: '.$e->getMessage(), previous: $e);
        }

        if ($answer === '') {
            $answer = 'I apologize, I was unable to process your request at this time. Please try again.';
        }

        $message = $user->chatbotMessages()->create([
            'question' => $question,
            'answer' => $answer,
            'session_id' => $conversationId,
        ]);

        return [
            'id' => $message->id,
            'conversation_id' => $conversationId,
            'question' => $question,
            'answer' => $answer,
            'rating' => null,
        ];
    }
}
