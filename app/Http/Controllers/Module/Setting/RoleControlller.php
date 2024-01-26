<?php

namespace App\Http\Controllers\Module\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

// use Yajra\DataTables\DataTables;

class RoleControlller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('module.setting.role.index');
    }

    /**
     * Display data as data table
     */
    public function data()
    {
        $query = Role::latest()->get();

        return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('action', function($query){
                    $html = "<a href='javascript:void(0)' onclick='renderView(`" . route('role.manage-permission', $query->id) . "`)'  class='btn icon btn-sm btn-outline-primary rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Manage Permission'>
                                <i class='fas fa-cogs'></i>
                            </a>";

                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    /**
     * Manage Permission
     */
    public function managePermission(string $id)
    {
        $role = Role::find($id);
        $data = [
            'role' => $role,
            'permissions' => Permission::all(),
            'myPermissions' => $role->getAllPermissions()->pluck('name')->toarray(),
        ];

        return view('module.setting.role.manage-permission', $data);
    }

    /**
     * Save manage permission
     */
    public function storePermission(Request $request)
    {
        try {
            $role = Role::find($request->role_id);
            $role->syncPermissions($request->permissions);
            return response()->json([
                'status' => 200,
                'message' => 'Update Permission Berhasil'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 401,
                'message' => 'Terjadi Kesalahan Pada Sistem!'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
