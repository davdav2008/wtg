<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed $city
 */
class SearchRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
        $this->merge([
            'city' => $this->city ? trim($this->city) : null,
        ]);
    }
}
