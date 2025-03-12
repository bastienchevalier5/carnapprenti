<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Log;
use Silber\Bouncer\Bouncer;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */

public function store(LoginRequest $request): RedirectResponse
{
    try {
        $request->authenticate();
        $user = auth()->user(); // Récupérer l'utilisateur authentifié

        // Vérifier si l'utilisateur a un rôle interdit
        if ($user->isAn('admin') || $user->isAn('qualite')) {
            auth()->logout(); // Déconnexion immédiate

            throw ValidationException::withMessages([
                'email' => 'Votre compte ne vous permet pas de vous connecter.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('accueil', absolute: false));
    } catch (ValidationException $e) {
        // Loguer la tentative de connexion échouée
        Log::warning('Tentative de connexion échouée.', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        throw $e; // Laisser l'exception être gérée normalement
    }
}



    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
