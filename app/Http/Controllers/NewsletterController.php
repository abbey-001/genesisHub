<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($request->email));

        $existing = NewsletterSubscription::where('email', $email)->first();

        if ($existing) {
            // They're already in — don't tell them to get lost, be chill
            if ($existing->is_active) {
                return back()->with('newsletter_info', "Relax, you're already on the list. We see you. 👀");
            }

            // They unsubscribed before — reactivate
            $existing->update(['is_active' => true, 'subscribed_at' => now()]);

            return back()->with('newsletter_success', "Welcome back! We missed you. Our promo emails definitely didn't. 🎉");
        }

        NewsletterSubscription::create([
            'email'         => $email,
            'subscribed_at' => now(),
        ]);

        return back()->with('newsletter_success', "Thank you for the sub🎊 Ws.");
    }

    public function unsubscribe(string $token)
    {
        $sub = NewsletterSubscription::where('token', $token)->firstOrFail();
        $sub->update(['is_active' => false]);

        return back()->with('newsletter_info', "You've unsubscribed. Bold move. We respect it. 🫡");
    }
}