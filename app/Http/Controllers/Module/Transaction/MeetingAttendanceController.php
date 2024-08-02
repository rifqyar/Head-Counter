<?php

namespace App\Http\Controllers\Module\Transaction;

use App\Helpers\DataAccessHelpers;
use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\Transaction\MeetingAttendance;
use App\Models\Transaction\QRDetail;
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
    public function formAttendance(Request $request)
    {
        $id = base64_decode($request->meeting_id);
        $avail = self::checkAvail($request);
        if ($avail == true) {
            return view('module.Transaction.MeetingAttendance.form-attendance', compact('id'));
        } else {
            return view('module.Transaction.MeetingAttendance.form-attendance', compact('id'));
            // return view('module.Transaction.MeetingAttendance.form-invalid');
        }
    }

    public function checkAvail(Request $request)
    {
        $id = base64_decode($request->meeting_id);
        $meeting = MeetingSchedule::where('trx_number', $id)->first();
        $qr = QRDetail::where('id', $request->qr_code)->first();
        $now = now();
        $start = $qr->qr_valid_start;
        $end = $qr->qr_valid_end;
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        $point = strtotime($now);

        if ($point >= $startTime && $point <= $endTime) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $schedule = MeetingSchedule::where('trx_number', $request->trx_number)->first();
            $checkAttendance = self::checkAttendance($request->trx_number, $schedule->kuota);
            if ($checkAttendance != 'ok') {
                if ($checkAttendance == 'scanned') return view('module.Transaction.MeetingAttendance.scanned');
                if ($checkAttendance == 'over') return view('module.Transaction.MeetingAttendance.over');
            } else {
                $data = [
                    'trx_metting_number' => $request->trx_number,
                    'name' => $request->name,
                    'phone_number' => $request->phone_number,
                    'jabatan' => $request->jabatan,
                    'mac_address' => DataAccessHelpers::getMac(),
                    'qr_path' => 0,
                    'scanned_qr' => 0,
                ];
                $attendance = MeetingAttendance::create($data);

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
                return response()->download(public_path() . '/qrcode/meeting_attendance/' . $output_file, $output_file);
            }
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

    public function checkAttendance($trx_number, $kuota)
    {
        $mac = DataAccessHelpers::getMac();
        $attendance = MeetingAttendance::where('trx_metting_number', $trx_number)->where('mac_address', $mac)->first();
        if ($attendance) {
            return 'scanned';
        }

        $totalAttendance = MeetingAttendance::where('trx_metting_number', $trx_number)->count();
        if ($totalAttendance == $kuota) {
            return 'over';
        }

        return 'ok';
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
