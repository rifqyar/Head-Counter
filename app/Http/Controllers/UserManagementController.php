<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\SyncUserRolesRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use App\Support\Security\RoleAuthority;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->visibleUsers($request)
            ->with(['hotel', 'roles', 'tokens'])
            ->orderBy('name')
            ->paginate(25);

        return $this->viewOrRedirect($request, 'domain.users.index', compact('users'));
    }

    public function create(Request $request, RoleAuthority $authority)
    {
        $this->authorize('viewAny', User::class);

        return $this->viewOrRedirect($request, 'domain.users.create', [
            'managedUser' => new User(['status' => 'ACTIVE']),
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'roles' => $authority->assignableRoles($request->user()),
        ]);
    }

    public function store(StoreManagedUserRequest $request, AuditLogger $auditLogger)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles'], $data['password_confirmation']);

        $user = DB::transaction(function () use ($request, $data, $roles, $auditLogger) {
            $user = User::create(array_merge($data, [
                'hotel_id' => $request->hotelId(),
                'password' => Hash::make($request->input('password')),
                'deactivated_at' => $data['status'] === 'INACTIVE' ? now() : null,
                'deactivated_by' => $data['status'] === 'INACTIVE' ? $request->user()->id : null,
            ]));
            $user->syncRoles($roles);
            $auditLogger->record('user.created', $user->hotel_id, $request->user()->id, $user, ['roles' => $roles], [], $user->only(['name', 'username', 'email', 'hotel_id', 'status']));

            return $user;
        });

        return redirect()->route('users.show', $user)->with('status', 'User created.');
    }

    public function show(Request $request, User $user, RoleAuthority $authority)
    {
        $this->authorize('manage', $user);
        $user->load(['hotel', 'roles', 'permissions', 'tokens']);

        return $this->viewOrRedirect($request, 'domain.users.show', [
            'managedUser' => $user,
            'effectivePermissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
            'tokenAbilities' => \App\Http\Requests\CreateUserTokenRequest::ABILITIES,
            'roles' => $authority->assignableRoles($request->user()),
        ]);
    }

    public function edit(Request $request, User $user, RoleAuthority $authority)
    {
        $this->authorize('manage', $user);

        return $this->viewOrRedirect($request, 'domain.users.edit', [
            'managedUser' => $user,
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'roles' => $authority->assignableRoles($request->user()),
        ]);
    }

    public function update(UpdateManagedUserRequest $request, User $user, AuditLogger $auditLogger)
    {
        $before = $user->only(['name', 'username', 'email', 'hotel_id', 'status']);
        $data = $request->validated();
        unset($data['password_confirmation']);

        DB::transaction(function () use ($request, $user, $data, $auditLogger, $before) {
            if (! $request->user()->isSuperAdmin()) {
                unset($data['hotel_id']);
            }
            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
                $auditLogger->record('user.password_reset_by_admin', $user->hotel_id, $request->user()->id, $user);
            } else {
                unset($data['password']);
            }
            if (($data['status'] ?? $user->status) === 'INACTIVE') {
                $data['deactivated_at'] = $user->deactivated_at ?: now();
                $data['deactivated_by'] = $request->user()->id;
                $user->tokens()->delete();
            } else {
                $data['deactivated_at'] = null;
                $data['deactivated_by'] = null;
            }

            $user->update($data);
            $auditLogger->record('user.updated', $user->hotel_id, $request->user()->id, $user, [], $before, $user->only(['name', 'username', 'email', 'hotel_id', 'status']));
        });

        return redirect()->route('users.show', $user)->with('status', 'User updated.');
    }

    public function syncRoles(SyncUserRolesRequest $request, User $user, AuditLogger $auditLogger)
    {
        $before = $user->roles()->pluck('name')->all();
        $roles = $request->validated()['roles'] ?? [];
        $user->syncRoles($roles);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $auditLogger->record('user.role_changed', $user->hotel_id, $request->user()->id, $user, [], ['roles' => $before], ['roles' => $roles]);

        return redirect()->route('users.show', $user)->with('status', 'User roles updated.');
    }

    public function activate(Request $request, User $user, AuditLogger $auditLogger)
    {
        $this->authorize('manage', $user);
        $user->forceFill(['status' => 'ACTIVE', 'deactivated_at' => null, 'deactivated_by' => null])->save();
        $auditLogger->record('user.activated', $user->hotel_id, $request->user()->id, $user);

        return back()->with('status', 'User activated.');
    }

    public function deactivate(Request $request, User $user, AuditLogger $auditLogger, RoleAuthority $authority)
    {
        $this->authorize('manage', $user);
        abort_if($user->isSuperAdmin() && $authority->activeSuperAdminCount() <= 1, 422, 'The last active super-admin cannot be deactivated.');

        $user->forceFill(['status' => 'INACTIVE', 'deactivated_at' => now(), 'deactivated_by' => $request->user()->id])->save();
        $user->tokens()->delete();
        $auditLogger->record('user.deactivated', $user->hotel_id, $request->user()->id, $user);
        $auditLogger->record('user.tokens_revoked', $user->hotel_id, $request->user()->id, $user, ['scope' => 'all']);

        return back()->with('status', 'User deactivated and tokens revoked.');
    }

    private function visibleUsers(Request $request)
    {
        $query = User::query();
        if (! $request->user()->isSuperAdmin()) {
            $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
            $query->where('hotel_id', $hotelId)->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('name', ['SUPER_ADMIN', 'Super Admin']));
        } elseif ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->integer('hotel_id'));
        }

        return $query;
    }
}
