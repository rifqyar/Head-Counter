<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        $permission = $this->route('id') ?: $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission)],
        ];
    }
}
