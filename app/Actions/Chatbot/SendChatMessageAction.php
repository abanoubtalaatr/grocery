<?php

namespace App\Actions\Chatbot;

use App\Models\ChatbotMessage;
use App\Models\User;
use App\Services\ChatbotService;

class SendChatMessageAction
{
    public function __construct(
        private readonly ChatbotService $chatbotService
    ) {}

    public function handle(User $user, array $data): array
    {
        $question = trim((string) $data['question']);
        $conversationId = $data['conversation_id'] ?? $data['session_id'] ?? null;

        $result = $this->chatbotService->chat(
            user: $user,
            question: $question,
            conversationId: $conversationId,
            locale: $data['locale'] ?? null
        );

        if (isset($data['rating'])) {
            ChatbotMessage::where('id', $result['id'])->update(['rating' => $data['rating']]);
            $result['rating'] = $data['rating'];
        }

        return $result;
    }
}