<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('provider'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $provider = $this->route('provider');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($provider->user_id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'divisi' => ['required', 'string', 'max:255'],
            'lantai' => ['required', 'string', 'max:50'],
            'no_hp' => ['required', 'string', 'max:20'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'nama_bank' => ['nullable', 'string', 'max:255'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'schedules' => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.is_active' => ['boolean'],
            'schedules.*.open_time' => ['nullable', 'date_format:H:i', 'required_if:schedules.*.is_active,1'],
            'schedules.*.close_time' => ['nullable', 'date_format:H:i', 'after:schedules.*.open_time', 'required_if:schedules.*.is_active,1'],
        ];
    }
}
