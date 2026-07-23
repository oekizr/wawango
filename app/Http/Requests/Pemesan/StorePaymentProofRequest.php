<?php

namespace App\Http\Requests\Pemesan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $this->user()->can('uploadPaymentProof', $order)) {
            return false;
        }

        $payment = $order->payment;

        return $payment
            && $payment->method !== 'cash'
            && in_array($payment->status, ['pending', 'ditolak'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bukti' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
