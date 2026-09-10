<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ImportRequest extends FormRequest
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
            'supplier' => [
                'required',
                'string',
                Rule::exists('suppliers', 'name'),
            ],
            'external_import_id' => ['required', 'string', 'max:255'],
            'sent_at' => ['required', 'date'],
            'offers' => ['required', 'array', 'min:1'],

            'offers.*.external_id' => ['required', 'string', 'max:255'],
            'offers.*.property' => ['required', 'array'],
            'offers.*.property.code' => ['required', 'string', 'max:255'],
            'offers.*.property.name' => ['required', 'string', 'max:255'],
            'offers.*.property.city' => ['required', 'string', 'max:255'],

            'offers.*.check_in' => ['required', 'date_format:Y-m-d'],
            'offers.*.check_out' => ['required', 'date_format:Y-m-d', 'after:offers.*.check_in'],
            'offers.*.max_guests' => ['required', 'integer', 'min:1'],
            'offers.*.price' => ['required', 'integer', 'min:0'],
            'offers.*.currency' => ['required', 'string', 'size:3'],
            'offers.*.available_units' => ['required', 'integer', 'min:0'],
            'offers.*.expires_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ($this->input('offers', []) as $index => $offer) {
                    if (!isset($offer['check_in'], $offer['check_out'])) {
                        continue;
                    }

                    if (strtotime($offer['check_out']) <= strtotime($offer['check_in'])) {
                        $validator->errors()->add(
                            "offers.{$index}.check_out",
                            "Check-out date must be after check-in date."
                        );
                    }
                }
            },
        ];
    }
}
