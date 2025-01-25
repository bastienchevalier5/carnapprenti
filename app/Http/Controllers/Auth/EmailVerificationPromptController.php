<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // Ensure there's a user logged in before calling hasVerifiedEmail()
        $user = $request->user();

        // Check if the user is logged in and has a verified email
        return $user && $user->hasVerifiedEmail()
                    ? redirect()->intended(route('accueil', absolute: false))
                    : view('auth.verify-email');
    }
}
