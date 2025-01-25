<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        // Get the authenticated user
        $user = $request->user();

        // Ensure there's an authenticated user
        if (!$user) {
            return redirect()->route('login')->with('error', 'You need to be logged in.');
        }

        // Check if the user has already verified their email
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('accueil', absolute: false));
        }

        // Send the email verification notification
        $user->sendEmailVerificationNotification();

        // Return with status message
        return back()->with('status', 'verification-link-sent');
    }
}
