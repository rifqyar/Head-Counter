<?php

namespace App\Http\Controllers\Module\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLegacyClientRequest;
use App\Http\Requests\UpdateLegacyClientRequest;
use App\Models\Module\MasterData\Client;
use Illuminate\Http\JsonResponse;
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
            ->editColumn('action', function ($query) {
                return "<a href='javascript:void(0)' onclick='renderView(`".route('client.edit', $query->id)."`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Client'>
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
    public function store(StoreLegacyClientRequest $request)
    {
        try {
            $client = Client::create($request->validated());

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $client,
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menyimpan client.');
        }
    }

    public function getDetail($id)
    {
        $client = Client::where('code', $id)->first();

        return response()->json([
            'status' => [
                'msg' => 'OK',
                'code' => JsonResponse::HTTP_OK,
            ],
            'data' => $client,
        ], JsonResponse::HTTP_OK);
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
        $client = Client::findOrFail($id);

        return view('module.MasterData.Client.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLegacyClientRequest $request, string $id)
    {
        $client = Client::findOrFail($id);

        try {
            $client->update($request->validated());

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $client,
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat memperbarui client.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Client::findOrFail($id)->delete();

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menghapus client.');
        }
    }
}
