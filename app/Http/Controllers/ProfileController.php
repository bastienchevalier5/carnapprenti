<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Crypt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->fill($request->validated());
        $user->password = Crypt::encryptString($request->mdp);
        $user->save();

        return redirect()->route('profile.edit')->with('success', __('Profil modifié avec succès'));
    }
}
