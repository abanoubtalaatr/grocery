<?php

namespace App\Http\Resources\Chatbot;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this['id'] ?? $this->id,
            'conversation_id' => $this['conversation_id'] ?? $this['session_id'] ?? $this->session_id,
            'session_id'      => $this['conversation_id'] ?? $this['session_id'] ?? $this->session_id, // Backward compatibility
            'question'        => $this['question'] ?? $this->question,
            'answer'          => $this['answer'] ?? $this->answer,
            'rating'          => $this['rating'] ?? $this->rating,
            'created_at'      => $this['created_at'] ?? $this->created_at,
        ];
    }
}