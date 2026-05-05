<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\AuditService;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        
        // Signature verification is mandatory — reject the request if the
        // webhook secret has not been configured in the environment.
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            Log::critical('Stripe webhook secret is not configured. Set STRIPE_WEBHOOK_SECRET in the environment.');
            return response()->json(['error' => 'Webhook endpoint misconfigured'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event['type'] ?? 'unknown',
        ]);

        /** @var array $eventData */
        $eventData    = $event->toArray();
        $stripeService = new StripeService();

        // Handle different event types
        try {
            $stripeObject = $eventData['data']['object'] ?? [];
            $stripeId     = $stripeObject['id'] ?? 'unknown';
            $customer     = $stripeObject['customer'] ?? null;

            switch ($eventData['type']) {
                case 'customer.subscription.created':
                    $stripeService->handleSubscriptionCreated($eventData);
                    AuditService::log(
                        AuditLog::SUBSCRIPTION_CREATED,
                        "Stripe subscription created: {$stripeId}",
                        null,
                        ['stripe_subscription_id' => $stripeId, 'customer' => $customer]
                    );
                    break;

                case 'customer.subscription.updated':
                    $stripeService->handleSubscriptionUpdated($eventData);
                    AuditService::log(
                        AuditLog::SUBSCRIPTION_UPDATED,
                        "Stripe subscription updated: {$stripeId}",
                        null,
                        ['stripe_subscription_id' => $stripeId, 'customer' => $customer, 'status' => $stripeObject['status'] ?? null]
                    );
                    break;

                case 'customer.subscription.deleted':
                    $stripeService->handleSubscriptionUpdated($eventData);
                    AuditService::log(
                        AuditLog::SUBSCRIPTION_CANCELLED,
                        "Stripe subscription cancelled: {$stripeId}",
                        null,
                        ['stripe_subscription_id' => $stripeId, 'customer' => $customer]
                    );
                    break;

                case 'payment_intent.succeeded':
                    $stripeService->handlePaymentSucceeded($eventData);
                    break;

                case 'payment_intent.payment_failed':
                    Log::warning('Payment failed', [
                        'payment_intent' => $eventData['data']['object']['id'] ?? 'unknown',
                    ]);
                    break;

                case 'invoice.paid':
                    $stripeService->handleInvoicePaid($eventData);
                    break;

                case 'invoice.payment_failed':
                    Log::warning('Invoice payment failed', [
                        'invoice' => $eventData['data']['object']['id'] ?? 'unknown',
                    ]);
                    break;

                default:
                    Log::info('Unhandled webhook event', [
                        'type' => $eventData['type'],
                    ]);
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'type' => $event['type'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
