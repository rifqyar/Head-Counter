<?php

namespace App\Http\Controllers\Module\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\SyncRolePermissionsRequest;
use App\Support\Audit\AuditLogger;
use App\Support\Security\RoleAuthority;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

// use Yajra\DataTables\DataTables;

class RoleControlller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(RoleAuthority $authority, Request $request)
    {
        return view('module.setting.role.index', [
            'roleCount' => $authority->assignableRoles($request->user())->count(),
            'permissionCount' => $authority->manageablePermissions($request->user())->count(),
        ]);
    }

    /**
     * Display data as data table
     */
    public function data(Request $request, RoleAuthority $authority)
    {
        $query = $authority->assignableRoles($request->user());

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', fn ($query) => '<span class="font-weight-semibold">'.$query->name.'</span>')
            ->addColumn('permissions_count', fn ($query) => $query->permissions()->count())
            ->editColumn('action', function ($query) {
                $html = "<a href='javascript:void(0)' onclick='renderView(`".route('role.manage-permission', $query->id)."`)' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' title='Manage permissions'>
                                <i class='fa fa-lock'></i> Permissions
                            </a>";

                return $html;
            })
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

    /**
     * Manage Permission
     */
    public function managePermission(Request $request, string $id, RoleAuthority $authority)
    {
        $role = Role::findOrFail($id);
        abort_unless($authority->canManageProtectedRole($request->user(), $role), 403);
        $data = [
            'role' => $role,
            'permissions' => $authority->manageablePermissions($request->user()),
            'myPermissions' => $role->getAllPermissions()->pluck('name')->toarray(),
        ];

        return view('module.setting.role.manage-permission', $data);
    }

    /**
     * Save manage permission
     */
    public function storePermission(SyncRolePermissionsRequest $request, AuditLogger $auditLogger)
    {
        try {
            $role = Role::findOrFail($request->validated('role_id'));
            $before = $role->permissions()->pluck('name')->all();
            $permissions = $request->validated('permissions') ?? [];
            $role->syncPermissions($permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $auditLogger->record('role.permissions_synced', null, $request->user()->id, $role, [], ['permissions' => $before], ['permissions' => $permissions]);

            return response()->json([
                'status' => 200,
                'message' => 'Update Permission Berhasil',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 401,
                'message' => 'Terjadi Kesalahan Pada Sistem!',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('module.setting.role.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request, AuditLogger $auditLogger)
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $auditLogger->record('role.created', null, $request->user()->id, $role, [], [], ['name' => $role->name]);

        return response()->json([
            'status' => 200,
            'message' => 'Role created.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
