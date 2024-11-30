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
        $rules['activites_pros'] = 'nullable|string';
        $rules['observations_apprenti'] = 'nullable|string';
        $rules['observations_tuteur'] = 'nullable|string';
        $rules['observations_referent'] = 'nullable|string';
        return $rules;
    }
}
