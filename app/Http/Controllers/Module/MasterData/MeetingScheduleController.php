<?php

namespace App\Http\Controllers\Module\MasterData;

use App\Enums\RoomStatusEnum;
use App\Helpers\DataAccessHelpers;
use App\Http\Controllers\Controller;
use App\Models\Module\MasterData\Client;
use App\Models\Module\MasterData\MeetingRooms;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\MasterData\Package;
use App\Models\Transaction\QRDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\DataTables;

// use Milon\Barcode\DNS2D;

class MeetingScheduleController extends Controller
{
    private function meetingQrFilename(string $trxNumber, ?int $sequence = null): string
    {
        $suffix = $sequence === null ? '' : " - {$sequence}";

        return 'QR Code - Meeting '.Str::slug($trxNumber).$suffix.' - '.Str::random(16).'.png';
    }

    private function meetingQrUrl(MeetingSchedule $schedule, QRDetail $qrCode): string
    {
        return route('meeting-attendance.form-attendance', [
            'meeting_id' => base64_encode($schedule->trx_number),
            'qr_code' => $qrCode->id,
            'qr_token' => pathinfo($qrCode->qr_path, PATHINFO_FILENAME),
        ]);
    }

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
            $schedule = $schedule->where('code_client', 'like', '%'.$request->client.'%');
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
                return $query->tgl_start.' - '.$query->tgl_end;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    private function renderAction($data)
    {
        $client = Client::where('code', $data->code_client)->first();
        $html = "
            <a href='javascript:void(0)' onclick='renderView(`".route('meeting-schedule.edit', $data->id)."`)'  class='btn icon btn-sm btn-outline-warning rounded-pill' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-title='Edit Schedule'>
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
        $createdFiles = [];

        try {
            $schedule = DB::transaction(function () use ($request, $trx_number, &$createdFiles) {
                $package = Package::where('kd_pck', $request->package)->firstOrFail();

                $schedule = MeetingSchedule::create([
                    'trx_number' => $trx_number,
                    'code_client' => $request->code_client,
                    'tgl_start' => $request->tgl_start,
                    'tgl_end' => $request->tgl_end,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'kuota' => $request->kuota * $package->count_qr,
                    'package' => $request->package,
                    'room' => $request->rooms[0],
                    'qr_path' => 0,
                ]);

                for ($i = 1; $i <= $package->count_qr; $i++) {
                    $outputFile = $this->meetingQrFilename($trx_number, $package->count_qr === 1 ? null : $i);
                    $qrCode = QRDetail::create([
                        'meeting_id' => $schedule->id,
                        'qr_path' => $outputFile,
                        'qr_valid_start' => Carbon::createFromFormat('Y-m-d H:i', $request->tgl_start.' '.$request->jam_mulai),
                        'qr_valid_end' => Carbon::createFromFormat('Y-m-d H:i', $request->tgl_end.' '.$request->jam_selesai),
                    ]);

                    $image = QrCode::format('png')
                        ->size(200)->errorCorrection('H')
                        ->generate($this->meetingQrUrl($schedule, $qrCode));

                    Storage::disk('qr_meeting_schedule')->put($outputFile, $image);
                    $createdFiles[] = $outputFile;
                }

                MeetingRooms::where('kd_room', $request->rooms[0])->update([
                    'room_availability' => RoomStatusEnum::Booked,
                ]);

                return $schedule;
            });

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $schedule,
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            foreach ($createdFiles as $file) {
                Storage::disk('qr_meeting_schedule')->delete($file);
            }

            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menyimpan jadwal meeting.');
        }
    }

    /**
     * Generate QR Code
     */
    public function generateQrCode(string $id)
    {
        $createdFiles = [];

        try {
            DB::transaction(function () use ($id, &$createdFiles) {
                $schedule = MeetingSchedule::findOrFail($id);
                $package = Package::where('kd_pck', $schedule->package)->firstOrFail();

                for ($i = 1; $i <= $package->count_qr; $i++) {
                    $outputFile = $this->meetingQrFilename($schedule->trx_number, $package->count_qr === 1 ? null : $i);
                    $qrCode = QRDetail::create([
                        'meeting_id' => $schedule->id,
                        'qr_path' => $outputFile,
                        'qr_valid_start' => $schedule->tgl_start.' '.$schedule->jam_mulai,
                        'qr_valid_end' => $schedule->tgl_end.' '.$schedule->jam_selesai,
                    ]);

                    $image = QrCode::format('png')
                        ->size(200)->errorCorrection('H')
                        ->generate($this->meetingQrUrl($schedule, $qrCode));

                    Storage::disk('qr_meeting_schedule')->put($outputFile, $image);
                    $createdFiles[] = $outputFile;
                }
            });

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            foreach ($createdFiles as $file) {
                Storage::disk('qr_meeting_schedule')->delete($file);
            }

            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat membuat QR meeting.');
        }
    }

    public function getQR($id)
    {
        $qr = QRDetail::where('meeting_id', $id)->get();

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
        $data = MeetingSchedule::findOrFail($id);
        $rooms = MeetingRooms::with('status')->get();

        return view('module.MasterData.MeetingSchedule.edit', compact('data', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $createdFiles = [];

        try {
            $schedule = DB::transaction(function () use ($request, &$createdFiles) {
                $currentData = MeetingSchedule::findOrFail($request->id);
                $package = Package::where('kd_pck', $currentData->package)->firstOrFail();
                $previousRoom = $currentData->room;

                $currentData->update([
                    'tgl_start' => $request->tgl_start,
                    'tgl_end' => $request->tgl_end,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'room' => $request->rooms[0],
                    'kuota' => $request->kuota * $package->count_qr,
                ]);

                if ($previousRoom !== $request->rooms[0]) {
                    MeetingRooms::where('kd_room', $previousRoom)->update([
                        'room_availability' => RoomStatusEnum::Available,
                    ]);
                }

                MeetingRooms::where('kd_room', $request->rooms[0])->update([
                    'room_availability' => RoomStatusEnum::Booked,
                ]);

                $currentQR = QRDetail::where('meeting_id', $currentData->id)->get();
                foreach ($currentQR as $index => $qrCode) {
                    $oldPath = $qrCode->qr_path;
                    $outputFile = $this->meetingQrFilename($currentData->trx_number, $currentQR->count() === 1 ? null : $index + 1);
                    $qrCode->update([
                        'qr_path' => $outputFile,
                        'qr_valid_start' => Carbon::createFromFormat('Y-m-d H:i', $request->tgl_start.' '.$request->jam_mulai),
                        'qr_valid_end' => Carbon::createFromFormat('Y-m-d H:i', $request->tgl_end.' '.$request->jam_selesai),
                    ]);

                    $image = QrCode::format('png')
                        ->size(200)->errorCorrection('H')
                        ->generate($this->meetingQrUrl($currentData, $qrCode));

                    Storage::disk('qr_meeting_schedule')->put($outputFile, $image);
                    $createdFiles[] = $outputFile;

                    if ($oldPath !== $outputFile) {
                        Storage::disk('qr_meeting_schedule')->delete($oldPath);
                    }
                }

                return $currentData;
            });

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
                'data' => $schedule,
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            foreach ($createdFiles as $file) {
                Storage::disk('qr_meeting_schedule')->delete($file);
            }

            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat memperbarui jadwal meeting.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $meetingSchedule = MeetingSchedule::findOrFail($id);

            // update meeting room status
            MeetingRooms::where('kd_room', $meetingSchedule->room)->update([
                'room_availability' => RoomStatusEnum::Available,
            ]);

            MeetingSchedule::findOrFail($id)->delete();

            DB::commit();

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => JsonResponse::HTTP_OK,
                ],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->safeErrorResponse($th, 'Terjadi kesalahan saat menghapus jadwal meeting.');
        }
    }
}
