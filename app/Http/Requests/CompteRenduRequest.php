<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRenduRequest extends FormRequest
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
        // Explicitly initialize the $rules array
        $rules = [
            'activites_pros' => 'nullable|string',
            'observations_apprenti' => 'nullable|string',
            'observations_tuteur' => 'nullable|string',
            'observations_referent' => 'nullable|string',
        ];

        return $rules;
    }
}
