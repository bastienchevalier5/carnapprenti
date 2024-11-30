<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LivretRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules['modele_id'] = "nullable|exists:modeles,id";
        $rules['apprenant_id'] = "nullable|exists:users,id";
        $rules['observation_apprenti_global'] = "nullable|string";
        $rules['observation_admin'] = 'nullable|string';
        return $rules;
    }
}
