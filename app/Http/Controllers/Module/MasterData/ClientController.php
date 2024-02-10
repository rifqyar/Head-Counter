<?php

namespace App\Http\Controllers\Module\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('module.MasterData.Client.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('module.MasterData.Client.add');
    }

    /**
     * Show data as datatable
     */
    public function data()
    {
        $query = Client::orderBy('code')->get();

        return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('action', function($query){
                    return "<a href='javascript:void(0)' onclick='renderView(`" . route('client.edit', $query->id) . "`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Client'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href='javascript:void(0)' onclick='deleteClient(`$query->id`)'  class='btn icon btn-sm btn-outline-danger rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Delete Client'>
                                <i class='fas fa-trash'></i>
                            </a>";
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            // 'code' => 'required|max:3|unique:m_client,code',
            'code' => 'required', 'max:3', Rule::unique('m_client')->where(function ($query) use ($request) {
                return $query->where('code', $request->code)->where('deleted_status', 0);
            }),
            // 'name' => 'required|unique:m_client,name',
            'name' => 'required', Rule::unique('m_client')->where(function ($query) use ($request) {
                return $query->where('name', $request->name)->where('deleted_status', 0);
            }),
            'contact_person' => 'required',
            'company_phone' => 'required',
            'email' => 'required|email|unique:m_client,email',
        ],[
            'code.unique' => 'Code Client sudah ada',
            'name.unique' => 'Nama Client sudah ada',
            'code.max' => 'Code Client tidak boleh lebih dari :max karakter'
        ]);

        try {

            $client = $request->all();
            $client = Client::create($client);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $client,
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
