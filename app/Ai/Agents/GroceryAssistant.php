<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CheckOffersTool;
use App\Ai\Tools\ListCategoriesTool;
use App\Ai\Tools\SearchProductsTool;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class GroceryAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    private const SYSTEM_INSTRUCTION = <<<'PROMPT'
        You are a helpful and friendly shopping assistant for Grocery+, an online grocery and meal delivery store.
        Use the available tools to look up real-time product information, offers, and categories from the database.
        Never invent product names, prices, or promo codes — always call a tool first.
        For order tracking, tell the user to check "My Orders" in the app.
        For payment, explain that the app supports card and cash on delivery.
        Keep your answers concise and friendly.
        PROMPT;

    public function __construct(public ?string $locale = null) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $localeHint = match ($this->locale) {
            'ar' => 'Respond in Arabic (العربية) unless the user writes in English.',
            'en' => 'Respond in English.',
            default => 'Respond in the same language the user uses.',
        };

        return trim(self::SYSTEM_INSTRUCTION)."\n".$localeHint;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SearchProductsTool,
            new CheckOffersTool,
            new ListCategoriesTool,
        ];
    }
}
