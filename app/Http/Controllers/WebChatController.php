<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WebChatController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbotService) {}

    public function index()
    {
        return view('chat');
    }

    public function send(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => ['required', 'string', 'max:1000'],
            ]);

            $message = trim($validated['message']);
            $user = $this->getOrCreateDemoUser();
            $conversationId = session('chat_conversation_id');

            $result = $this->chatbotService->chat(
                user: $user,
                question: $message,
                conversationId: $conversationId,
                locale: null,
            );

            session(['chat_conversation_id' => $result['conversation_id']]);

            return response()->json([
                'answer' => $result['answer'],
            ]);

        } catch (Throwable $e) {
            Log::error('Web chat error', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to get a response. Please try again.',
            ], 500);
        }
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget('chat_conversation_id');

        return response()->json(['reset' => true]);
    }

    private function getOrCreateDemoUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'webchat@grocery.demo'],
            [
                'firstname' => 'Web',
                'lastname' => 'Chat Demo',
                'username' => 'webchat_demo',
                'password' => Hash::make(Str::random(32)),
            ]
        );
    }

}
