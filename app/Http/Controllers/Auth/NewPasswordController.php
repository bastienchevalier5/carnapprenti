<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Tentative de réinitialisation du mot de passe de l'utilisateur
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // Récupération du mot de passe, avec une valeur par défaut de chaîne vide
                $password = $request->password ?? ''; // Assurer que c'est une chaîne

                // Vérification que le mot de passe est bien une chaîne
                if (! is_string($password)) {
                    $password = ''; // Assurer que c'est une chaîne
                }

                // Utilisation de Hash::make avec un mot de passe garanti en tant que chaîne
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $statusMessage = is_string($status) ? $status : ''; // Assurer que c'est une chaîne

        // Redirection après réinitialisation du mot de passe
        return $statusMessage === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($statusMessage)) // Utilisation directe de $status
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($statusMessage)]);
    }
}
