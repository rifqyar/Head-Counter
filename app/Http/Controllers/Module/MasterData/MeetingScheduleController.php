<?php

namespace App\Http\Controllers\Module\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\Client;
use App\Models\Module\MasterData\MeetingSchedule;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use App\Helpers\DataAccessHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\DataTables;

class MeetingScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('module.MasterData.MeetingSchedule.index');
    }

    public function data(Request $request)
    {
        $schedule = MeetingSchedule::orderBy('tgl_meeting');

        if($request->client != null){
            $schedule = $schedule->where('code_client', 'like', '%'.$request->client.'%');
        }

        if($request->tgl != date('Y-m-d')){
            $schedule = $schedule->orWhereDate('tgl_meeting', $request->tgl);
        }

        return DataTables::of($schedule->get())
                ->addIndexColumn()
                ->editColumn('action', function($query){
                    return self::renderAction($query);
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    private function renderAction($data)
    {
        $client = Client::where('code', $data->code_client)->first();
        $html = "
            <a href='javascript:void(0)' onclick='renderView(`" . route('meeting-schedule.edit', $data->id) . "`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Schedule'>
                <i class='fas fa-pencil'></i>
            </a>
            <a href='javascript:void(0)' onclick='deleteSchedule(`$data->id`)'  class='btn icon btn-sm btn-outline-danger rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Delete Schedule'>
                <i class='fas fa-trash'></i>
            </a>
        ";

        if($data->qr_path != null){
            $pathQr = asset("qrcode/meeting_schedule/$data->qr_path");
            $pathQr = base64_encode($pathQr);
            $html .= "<a href='javascript:void(0)' onclick='showQr(this)' data-qr_path='$pathQr' data-tag='Meeting $client->name at $data->tgl_meeting'  class='btn icon btn-sm btn-outline-info rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Show QR Code'>
                        <i class='bi bi-qr-code'></i>
                    </a>";
        } else {
            $html .= "<a href='javascript:void(0)' onclick='generateQr(`$data->id`)'  class='btn icon btn-sm btn-outline-primary rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Generate QR Code'>
                        <i class='bi bi-qr-code-scan'></i>
                    </a>";
        }

        return $html;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['client'] = Client::orderBy('code')->get();

        $now = Carbon::now()->format('Y-m-d');
        $end = Carbon::now()->addDays(30)->format('Y-m-d');

        $begin = new DateTime($now);
        $end = new DateTime($end);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $data['avail_date'] = [];
        foreach ($period as $dt) {
            $existingSchedule = MeetingSchedule::whereDate('tgl_meeting', $dt->format('Y-m-d'))->count();
            if($existingSchedule == 0){
                array_push($data['avail_date'], $dt->format('Y-m-d'));
            }
        }

        return view('module.MasterData.MeetingSchedule.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $trx_number = DataAccessHelpers::generateTransactionNumber($request->code_client);
        $encodedTrx = base64_encode($trx_number);

        try {
            $output_file = null;
            $qrCodeIsi = route('meeting-attendance.form-attendance', $encodedTrx);

            if($request->generateQR == 'ya'){
                $image = QrCode::format('png')
                 ->size(200)->errorCorrection('H')
                 ->generate($qrCodeIsi);

                $output_file = 'QR Code - Meeting ' . $request->code_client . ' - ' . $request->tgl_meeting . '.png';
                Storage::disk('qr_meeting_schedule')->put($output_file, $image);
            }

            $data = [
                'trx_number' => $trx_number,
                'code_client' => $request->code_client,
                'tgl_meeting' => $request->tgl_meeting,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'kuota' => $request->kuota,
                'qr_path' => $output_file
            ];

            $schedule = MeetingSchedule::create($data);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $schedule
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
     * Generate QR Code
     */
    public function generateQrCode(string $id)
    {
        try {
            $schedule = MeetingSchedule::find($id);
            $client = Client::where('code', $schedule->code_client)->first();

            $encodedTrx = base64_encode($schedule->trx_number);
            $qrCodeIsi = route('meeting-attendance.form-attendance', $encodedTrx);

            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($qrCodeIsi);

            $output_file = 'QR Code - Meeting ' . $client->code . ' - ' . $schedule->tgl_meeting . '.png';
            $storageQr = Storage::disk('qr_meeting_schedule');

            if(!$storageQr->exists($output_file)){
                $storageQr->delete($output_file);
            }
            $storageQr->put($output_file, $image);

            $schedule->update([
                'qr_path' => $output_file
            ]);

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
