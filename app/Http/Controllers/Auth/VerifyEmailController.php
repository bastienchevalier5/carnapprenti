<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to verify your email.');
        }

        // If the email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('accueil', absolute: false).'?verified=1');
        }

        // Mark the email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Redirect to intended route
        return redirect()->intended(route('accueil', absolute: false).'?verified=1');
    }


}
