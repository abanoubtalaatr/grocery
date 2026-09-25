<?php

namespace App\Actions\Chatbot;

use App\Models\ChatbotMessage;
use App\Models\User;

class GetChatHistoryAction
{
    public function handle(User $user, int $perPage): array
    {
        $messages = $user->chatbotMessages()
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

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'from' => $messages->firstItem(),
                'to' => $messages->lastItem(),
            ],
        ];
    }
}