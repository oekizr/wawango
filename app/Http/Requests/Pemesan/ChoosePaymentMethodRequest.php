<?php

namespace App\Http\Requests\Pemesan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChoosePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $this->user()->can('choosePaymentMethod', $order)) {
            return false;
        }

        // Payment method can only be chosen once the provider has confirmed
        // the order (not while it's still "menunggu"), and only once - not
        // if a payment record already exists.
        return $order->status !== 'menunggu'
            && $order->status !== 'dibatalkan'
            && ! $order->payment;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
        ];
    }
}
