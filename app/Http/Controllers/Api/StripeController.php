<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function createSetupIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        if (!$user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $intent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
        ]);

        return response()->json(['clientSecret' => $intent->client_secret]);
    }

    public function listCards(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        if (!$user->stripe_customer_id) return response()->json([]);

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return response()->json($cards->data);
    }


    public function chargeSavedCard(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd',
            'customer' => $user->stripe_customer_id,
            'payment_method' => $request->payment_method_id,
            'off_session' => true,
            'confirm' => true,
        ]);

        return response()->json(['status' => 'success', 'payment_intent' => $paymentIntent]);
    }

    public function deleteCard(Request $request, $id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($id);
        $paymentMethod->detach();

        return response()->json(['status' => 'deleted']);
    }
}
