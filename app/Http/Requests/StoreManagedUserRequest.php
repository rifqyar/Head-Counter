<?php

namespace App\Http\Requests;

use App\Support\Security\RoleAuthority;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('user.manage');
    }

    public function rules(): array
    {
        $actor = $this->user();
        $hotelId = app(TenantContext::class)->hotelId() ?: $actor->hotel_id;
        $assignableRoles = app(RoleAuthority::class)->assignableRoles($actor)->pluck('name')->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'hotel_id' => [
                $actor->isSuperAdmin() ? 'nullable' : 'prohibited',
                Rule::exists('hotels', 'id')->where('status', 'ACTIVE'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($assignableRoles)],
        ];
    }

    public function hotelId(): ?int
    {
        if ($this->user()->isSuperAdmin()) {
            return $this->filled('hotel_id') ? (int) $this->input('hotel_id') : null;
        }

        return app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;
    }
}
