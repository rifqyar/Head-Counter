<?php

namespace App\Http\Controllers\Module\MasterData;

use App\Enums\RoomStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\Client;
use App\Models\Module\MasterData\MeetingSchedule;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use App\Helpers\DataAccessHelpers;
use App\Models\Module\MasterData\MeetingRooms;
use App\Models\Module\MasterData\Package;
use App\Models\Transaction\QRDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\DataTables;
// use Milon\Barcode\DNS2D;

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
        $schedule = MeetingSchedule::orderBy('tgl_start')->with('ruangan')->with('paket')->with('qr');

        if ($request->client != null) {
            $schedule = $schedule->where('code_client', 'like', '%' . $request->client . '%');
        }

        if ($request->tgl != date('Y-m-d')) {
            $schedule = $schedule->orWhereDate('tgl_start', $request->tgl);
        }

        return DataTables::of($schedule->get())
            ->addIndexColumn()
            ->editColumn('action', function ($query) {
                return self::renderAction($query);
            })
            ->editColumn('tgl_meeting', function ($query) {
                return $query->tgl_start . ' - ' . $query->tgl_end;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    private function renderAction($data)
    {
        $client = Client::where('code', $data->code_client)->first();
        $html = "
            <a href='javascript:void(0)' onclick='renderView(`" . route('meeting-schedule.edit', $data->id) . "`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Schedule'>
                <i class='fas fa-edit'></i>
            </a>
            <a href='javascript:void(0)' onclick='deleteSchedule(`$data->id`)'  class='btn icon btn-sm btn-outline-danger rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Delete Schedule'>
                <i class='fas fa-trash'></i>
            </a>
        ";
        if (count($data->qr) > 0) {
            // $pathQr = asset("qrcode/meeting_schedule/$data->qr_path");
            // $pathQr = base64_encode($pathQr);
            $html .= "<a href='javascript:void(0)' onclick='showQr(this, `$data->id`)' data-tag='Meeting $client->name at $data->tgl_start - $data->tgl_end'  class='btn icon btn-sm btn-outline-info rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Show QR Code'>
                        <i class='fas fa-qrcode'></i>
                    </a>";
        } else {
            $html .= "<a href='javascript:void(0)' onclick='generateQr(`$data->id`)'  class='btn icon btn-sm btn-outline-primary rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Generate QR Code'>
                        <i class='fas fa-qrcode'></i>
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
        $data['package'] = Package::get();
        $data['rooms'] = MeetingRooms::with('status')->get();
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
            $package = Package::where("kd_pck", $request->package)->first();

            $data = [
                'trx_number' => $trx_number,
                'code_client' => $request->code_client,
                'tgl_start' => $request->tgl_start,
                'tgl_end' => $request->tgl_end,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'kuota' => $request->kuota * $package->count_qr,
                'package' => $request->package,
                'room' => $request->rooms[0],
                'qr_path' => 0
            ];

            $schedule = MeetingSchedule::create($data);

            $insertQR = [];
            if ($package->count_qr == 1) {
                $output_file = 'QR Code - Meeting ' . $request->code_client . ' - ' . $request->tgl_start . '.png';
                array_push($insertQR, [
                    'meeting_id' => $schedule->id,
                    'qr_path' => $output_file,
                    'qr_valid_start' => Carbon::createFromFormat('Y-m-d H:i',  $request->tgl_start . ' ' . $request->jam_mulai),
                    'qr_valid_end' => Carbon::createFromFormat('Y-m-d H:i',  $request->tgl_end . ' ' . $request->jam_selesai),
                ]);
            } else {
                for ($i = 1; $i <= $package->count_qr; $i++) {
                    $output_file = 'QR Code - Meeting ' . $request->code_client . ' - ' . $request->tgl_start . ' - ' . $i . '.png';
                    array_push($insertQR, [
                        'meeting_id' => $schedule->id,
                        'qr_path' => $output_file,
                        'qr_valid_start' => Carbon::createFromFormat('Y-m-d H:i',  $request->tgl_start . ' ' . $request->jam_mulai),
                        'qr_valid_end' => Carbon::createFromFormat('Y-m-d H:i',  $request->tgl_end . ' ' . $request->jam_selesai),
                    ]);
                }
            }

            foreach ($insertQR as $val) {
                $qrCode = QRDetail::create($val);
                $qrCodeIsi = route('meeting-attendance.form-attendance', ['meeting_id' => $encodedTrx, 'qr_code' => $qrCode->id]);

                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($qrCodeIsi);

                Storage::disk('qr_meeting_schedule')->put($output_file, $image);
            }

            // update meeting room status
            MeetingRooms::where('kd_room', $request->rooms[0])->update([
                'room_availability' => RoomStatusEnum::Booked
            ]);

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
        DB::beginTransaction();
        try {
            $schedule = MeetingSchedule::find($id);
            $client = Client::where('code', $schedule->code_client)->first();
            $package = Package::where('kd_pck', $schedule->package)->first();
            $encodedTrx = base64_encode($schedule->trx_number);

            $insertQR = [];
            if ($package->count_qr == 1) {
                $output_file = 'QR Code - Meeting ' . $client->code . ' - ' . $schedule->tgl_start . '.png';
                array_push($insertQR, [
                    'meeting_id' => $schedule->id,
                    'qr_path' => $output_file,
                    'qr_valid_start' => $schedule->tgl_start . ' ' . $schedule->jam_mulai,
                    'qr_valid_end' => $schedule->tgl_end . ' ' . $schedule->jam_selesai,
                ]);
            } else {
                for ($i = 1; $i <= $package->count_qr; $i++) {
                    $output_file = 'QR Code - Meeting ' . $client->code . ' - ' . $schedule->tgl_start . ' - ' . $i . '.png';
                    array_push($insertQR, [
                        'meeting_id' => $schedule->id,
                        'qr_path' => $output_file,
                        'qr_valid_start' => $schedule->tgl_start . ' ' . $schedule->jam_mulai,
                        'qr_valid_end' => $schedule->tgl_end . ' ' . $schedule->jam_selesai,
                    ]);
                }
            }
            foreach ($insertQR as $val) {
                $qrCode = QRDetail::create($val);
                $qrCodeIsi = route('meeting-attendance.form-attendance', ['meeting_id' => $encodedTrx, 'qr_code' => $qrCode->id]);

                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($qrCodeIsi);

                Storage::disk('qr_meeting_schedule')->put($output_file, $image);
            }

            DB::commit();
            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
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

    public function getQR($id)
    {
        $qr = QRDetail::where("meeting_id", $id)->get();
        return response()->json([
            'status' => [
                'msg' => 'OK',
                'code' => JsonResponse::HTTP_OK,
            ],
            'data' => $qr,
            'asset_path' => asset('qrcode/meeting_schedule/'),
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
