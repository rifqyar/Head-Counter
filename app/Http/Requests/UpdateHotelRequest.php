<?php

namespace App\Http\Requests;

use App\Enums\HotelStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'max:20', 'alpha_dash', Rule::unique('hotels', 'code')->ignore($this->route('hotel'))],
            'name' => ['required', 'max:255'],
            'address' => ['nullable', 'string'],
            'timezone' => ['required', 'timezone'],
            'status' => ['required', Rule::enum(HotelStatus::class)],
            'settings' => ['nullable', 'array'],
        ];
    }
}
