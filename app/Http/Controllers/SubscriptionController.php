<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Verify subscriber's email address using token.
     */
    public function verify(Request $request, string $token)
    {
        $subscriber = $this->subscriptionService->verifySubscriber($token);

        if ($subscriber) {
            return view('subscription.verified', [
                'subscriber' => $subscriber,
                'success' => true,
            ]);
        }

        return view('subscription.verified', [
            'subscriber' => null,
            'success' => false,
            'message' => 'Invalid or expired verification link.',
        ]);
    }

    /**
     * Unsubscribe using email.
     */
    public function unsubscribe(Request $request, string $email)
    {
        $success = $this->subscriptionService->unsubscribe($email);

        return view('subscription.unsubscribed', [
            'success' => $success,
            'email' => $email,
        ]);
    }
}
