<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('cancel', $this->route('order'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', Rule::in(['toko_tutup', 'menu_habis', 'barang_tidak_ada', 'cuaca', 'lainnya'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
