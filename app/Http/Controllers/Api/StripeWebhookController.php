<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

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

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader ?? '',
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid payload or signature.', 400);
        }

        try {
            $this->webhookService->handleEvent($event);
        } catch (\Throwable $e) {
            report($e);

            return response('Handler error.', 500);
        }

        return response('OK', 200);
    }
}
