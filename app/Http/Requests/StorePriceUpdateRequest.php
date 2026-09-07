<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePriceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $amount = $this->input('amount_cents');
        if (is_string($amount) && ctype_digit($amount)) {
            $this->merge(['amount_cents' => (int) $amount]);
        }

        $currency = $this->input('currency');
        if (is_string($currency)) {
            $this->merge(['currency' => strtoupper($currency)]);
        }
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64', 'exists:products,sku'],
            'store_code' => ['required', 'string', 'max:64', 'exists:stores,code'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'recorded_at' => ['sometimes', 'date'],
            'trace_id' => ['sometimes', 'uuid'],
        ];
    }
}
