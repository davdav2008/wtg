<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city'       => ['nullable', 'string', 'max:255'],
            'check_in'   => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out'  => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests'     => ['required', 'integer', 'min:1', 'max:50'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'       => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $city = $this->input('city');
        $this->merge([
            'city' => $city ? trim($city) : null,
        ]);
    }
}
