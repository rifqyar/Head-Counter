<?php

namespace App\Http\Controllers\Module\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                ->editColumn('action', function($query){
                    $html = "<a href='javascript:void(0)' onclick='renderView(`" . route('permission.edit', $query->id) . "`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Permission'>
                                <i class='fas fa-pencil'></i>
                            </a>
                            <a href='javascript:void(0)' onclick='renderView(`" . route('permission.destroy', $query->id) . "`)'  class='btn icon btn-sm btn-outline-danger rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Delete Permission'>
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
        try {
            $permission = Permission::create(['name' => $request->name]);
            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => [
                    'msg' => 'Err',
                    'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                ],
                'data' => null,
                'err_detail' => $th,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
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
