<?php

namespace App\Http\Controllers\Module\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('module.setting.permission.index');
    }

    /**
     * Display data as data table
     */
    public function data()
    {
        $query = Permission::orderBy('name')->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('action', function ($query) {
                $html = "<a href='javascript:void(0)' onclick='renderView(`".route('permission.edit', $query->id)."`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Permission'>
                                <i class='fas fa-pencil'></i>
                            </a>
                            <a href='javascript:void(0)' onclick='renderView(`".route('permission.destroy', $query->id)."`)'  class='btn icon btn-sm btn-outline-danger rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Delete Permission'>
                                <i class='fas fa-trash'></i>
                            </a>";

                return $html;
            })
            ->rawColumns(['action'])
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:255', Rule::unique('permissions', 'name')],
        ]);

        try {
            Permission::create(['name' => $request->name]);

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
    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $this->validate($request, [
            'name' => ['required', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        try {
            $permission->update(['name' => $request->name]);

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
    public function destroy(string $id)
    {
        try {
            Permission::findOrFail($id)->delete();

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
