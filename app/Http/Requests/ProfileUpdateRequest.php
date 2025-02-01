<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : null; // Check if user exists before accessing id

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId), // Use $userId safely here
            ],
            'mdp' => ['required', Password::defaults()],
            'mdpConfirm' => ['required', Password::defaults(), 'same:mdp']
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être dans un format valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'mdp.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mdpConfirm.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mdpConfirm.same' => 'Les mots de passes ne correspondent pas.',
        ];
    }

}
