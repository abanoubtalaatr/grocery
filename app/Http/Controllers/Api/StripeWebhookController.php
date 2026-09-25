<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeWebhookService $webhookService
    ) {}

    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return response('Webhook not configured.', 500);
        }

        $handled = $this->webhookService->handlePayload(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            $secret
        );

        if (! $handled) {
            return response('Invalid payload or signature.', 400);
        }

        return response('OK', 200);
    }
}
