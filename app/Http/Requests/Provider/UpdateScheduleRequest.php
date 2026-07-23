<?php

namespace App\Http\Requests\Provider;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->provider()->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedules' => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.is_active' => ['boolean'],
            'schedules.*.open_time' => ['nullable', 'date_format:H:i', 'required_if:schedules.*.is_active,1'],
            'schedules.*.close_time' => ['nullable', 'date_format:H:i', 'after:schedules.*.open_time', 'required_if:schedules.*.is_active,1'],
        ];
    }
}
