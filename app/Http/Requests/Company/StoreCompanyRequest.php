<?php

namespace App\Http\Requests\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Only authenticated administrators reach this request (routes are behind `auth`).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'url:http,https', 'max:255'],
            'logo' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(2 * 1024)
                    ->dimensions(Rule::dimensions()->minWidth(100)->minHeight(100)),
            ],
        ];
    }

    /**
     * Normalise input before validation (e.g. add a scheme to bare domains).
     */
    protected function prepareForValidation(): void
    {
        $website = trim((string) $this->input('website'));

        if ($website !== '' && ! preg_match('#^https?://#i', $website)) {
            $website = 'https://'.$website;
        }

        $this->merge([
            'email' => $this->filled('email') ? strtolower(trim((string) $this->input('email'))) : null,
            'website' => $website !== '' ? $website : null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logo.dimensions' => 'The logo must be at least 100×100 pixels.',
        ];
    }
}
