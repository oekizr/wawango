<?php

namespace App\Http\Requests\Pemesan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'store_id' => ['required', 'exists:stores,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
