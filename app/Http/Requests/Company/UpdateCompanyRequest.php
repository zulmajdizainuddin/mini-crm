<?php

namespace App\Http\Requests\Company;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateCompanyRequest extends StoreCompanyRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }
}
