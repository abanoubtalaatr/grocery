<?php

namespace Doc;

use OpenApi\Attributes as OA;

class StripeWebhookDoc
{
    #[OA\Post(
        path: '/api/stripe/webhook',
        summary: 'Stripe webhook endpoint (not for direct client use)',
        description: 'Receives Stripe event notifications (e.g. checkout.session.completed, payment_intent events) used to keep order/payment status in sync. The request must be sent by Stripe with a valid Stripe-Signature header verified against the configured webhook secret; this endpoint is not called by the mobile client.',
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Stripe's raw event payload (see Stripe webhook documentation)",
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Event handled ("OK")'),
            new OA\Response(response: 400, description: 'Invalid payload or signature'),
            new OA\Response(response: 500, description: 'Webhook not configured, or handler error'),
        ]
    )]
    public function handle() {}
}
