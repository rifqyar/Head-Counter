<?php

namespace App\Http\Controllers\Module\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Support\Audit\AuditLogger;
use App\Support\Security\RoleAuthority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RoleAuthority $authority)
    {
        return view('module.setting.permission.index', [
            'permissionCount' => $authority->manageablePermissions($request->user())->count(),
            'roleCount' => \Spatie\Permission\Models\Role::count(),
        ]);
    }

    /**
     * Display data as data table
     */
    public function data()
    {
        $query = Permission::orderBy('name')->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', fn ($query) => '<span class="font-weight-semibold">'.$query->name.'</span>')
            ->addColumn('roles_count', fn ($query) => $query->roles()->count())
            ->editColumn('action', function ($query) {
                $html = "<a href='javascript:void(0)' onclick='renderView(`".route('permission.edit', $query->id)."`)' class='btn btn-sm btn-outline-warning mr-1' data-toggle='tooltip' title='Edit permission'>
                                <i class='fa fa-pencil'></i>
                            </a>
                            <a href='javascript:void(0)' onclick='prompt(`delete`, `Permission`, function(confirm){ if(confirm){ apiCall(`setting/permission/destroy/".$query->id.'`, `GET`, ``, null, null, null, true, function(){ renderView(`'.route('setting.permission')."`); }); } })' class='btn btn-sm btn-outline-danger' data-toggle='tooltip' title='Delete permission'>
                                <i class='fa fa-trash'></i>
                            </a>";

                return $html;
            })
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('module.setting.permission.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request, AuditLogger $auditLogger)
    {
        try {
            $permission = Permission::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $auditLogger->record('permission.created', null, $request->user()->id, $permission, [], [], ['name' => $permission->name]);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menyimpan permission.');
        }
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
        $permission = Permission::findOrFail($id);

        return view('module.setting.permission.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, string $id, AuditLogger $auditLogger)
    {
        $permission = Permission::findOrFail($id);

        try {
            $before = $permission->only(['name']);
            $permission->update(['name' => $request->validated('name')]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $auditLogger->record('permission.updated', null, $request->user()->id, $permission, [], $before, $permission->only(['name']));

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat memperbarui permission.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, AuditLogger $auditLogger)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        $permission = Permission::findOrFail($id);
        abort_if(
            $permission->roles()->exists() || DB::table('model_has_permissions')->where('permission_id', $permission->id)->exists(),
            422,
            'Assigned permissions cannot be deleted.'
        );

        try {
            $before = $permission->only(['name']);
            $permission->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $auditLogger->record('permission.deleted_or_deactivated', null, $request->user()->id, null, ['permission_id' => $id], $before, []);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menghapus permission.');
        }
    }
}
