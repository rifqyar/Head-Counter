<?php

namespace App\Http\Controllers\Module\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\Transaction\MeetingAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MeetingAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Show Form attendance
     */
    public function formAttendance(string $id)
    {
        $id = base64_decode($id);
        return view('module.Transaction.MeetingAttendance.form-attendance', compact('id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'trx_metting_number' => $request->trx_number,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->email,
            ];

            $attendance = MeetingAttendance::create($data);
            $schedule = MeetingSchedule::where('trx_number', $request->trx_number)->first();

            $output_file = null;
            $qrCodeIsi = $attendance->id;

            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($qrCodeIsi);

            $output_file = 'QR Code - Attendance ' . $schedule->code_client . ' - ' . $request->name . '.png';
            Storage::disk('qr_meeting_attendance')->put($output_file, $image);

            $attendance = MeetingAttendance::find($attendance->id);
            $attendance->update([
                'qr_path' => $output_file
            ]);

            DB::commit();
            return response()->download(public_path(). '/qrcode/meeting_attendance/'.$output_file, $output_file);
        } catch (\Throwable $th) {
            DB::rollBack();
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
