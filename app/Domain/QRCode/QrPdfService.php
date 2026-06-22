<?php

namespace App\Domain\QRCode;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\Transaction\MeetingAttendance as LegacyMeetingAttendance;
use App\Support\Branding\HotelLogo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrPdfService
{
    public function __construct(private readonly HotelLogo $hotelLogo) {}

    public function storeMeetingPdf(MeetingEvent $meeting, string $url): string
    {
        $meeting->loadMissing([
            'hotel',
            'booking.client',
            'meetingRoom',
            'packageAssignments.package',
        ]);

        $path = 'qrcodes/meeting-'.$meeting->id.'-'.Str::uuid().'.pdf';
        Storage::disk('public')->put($path, $this->meetingPdf($meeting, $url)->output());

        return $path;
    }

    public function meetingPdf(MeetingEvent $meeting, string $url)
    {
        $meeting->loadMissing([
            'hotel',
            'booking.client',
            'meetingRoom',
            'packageAssignments.package',
        ]);

        return $this->meetingPdfWithQrDataUri($meeting, $this->qrDataUri($url));
    }

    public function meetingPdfWithStoredQr(MeetingEvent $meeting): mixed
    {
        $meeting->loadMissing([
            'hotel',
            'booking.client',
            'meetingRoom',
            'packageAssignments.package',
        ]);

        $contents = Storage::disk('public')->get($meeting->meeting_qr_path);
        $mime = str_ends_with((string) $meeting->meeting_qr_path, '.svg') ? 'image/svg+xml' : 'image/png';

        return $this->meetingPdfWithQrDataUri($meeting, 'data:'.$mime.';base64,'.base64_encode($contents));
    }

    public function meetingPdfWithQrDataUri(MeetingEvent $meeting, string $qrDataUri): mixed
    {
        return $this->loadTicketPdf('Meeting Attendance QR', $meeting->hotel, $qrDataUri, [
            'Purpose' => 'Participant registration and meeting check-in',
            'Hotel' => $meeting->hotel?->name,
            'Client' => $meeting->booking?->client?->company_name,
            'Booking ID' => $meeting->booking?->booking_number,
            'Meeting' => $meeting->event_name,
            'Schedule' => $this->scheduleText($meeting->start_at, $meeting->end_at),
            'Room' => $meeting->meetingRoom?->name,
            'Package' => $meeting->packageAssignments->first()?->package?->name,
            'Expected Participants' => $meeting->expected_participants,
            'QR Last Four' => $meeting->meeting_qr_token_last_four,
            'Valid Until' => $meeting->meeting_qr_expires_at?->format('d M Y H:i'),
        ], 'Scan this QR to open the participant registration form for this meeting.');
    }

    public function participantPdf(Participant $participant, string $url)
    {
        $participant->loadMissing([
            'hotel',
            'meetingEvent.booking.client',
            'meetingEvent.meetingRoom',
            'activeQrCredential',
        ]);

        $meeting = $participant->meetingEvent;
        $credential = $participant->activeQrCredential;

        return $this->loadTicketPdf('Participant QR', $participant->hotel, $this->qrDataUri($url), [
            'Purpose' => 'Participant meal/session scan credential',
            'Hotel' => $participant->hotel?->name,
            'Participant' => $participant->full_name,
            'Participant No.' => $participant->participant_number,
            'Company' => $participant->company_name,
            'Email' => $participant->email,
            'Phone' => $participant->phone,
            'Client' => $meeting?->booking?->client?->company_name,
            'Meeting' => $meeting?->event_name,
            'Schedule' => $meeting ? $this->scheduleText($meeting->start_at, $meeting->end_at) : null,
            'Room' => $meeting?->meetingRoom?->name,
            'Credential Last Four' => $credential?->token_last_four,
            'Issued' => $credential?->issued_at?->format('d M Y H:i'),
            'Expires' => $credential?->expires_at?->format('d M Y H:i'),
        ], 'Keep this QR private. It belongs to one participant and duplicate scans are protected.');
    }

    public function storeParticipantPdf(Participant $participant, string $url): string
    {
        $path = 'qrcodes/participant-'.$participant->id.'-'.Str::uuid().'.pdf';
        Storage::disk('public')->put($path, $this->participantPdf($participant, $url)->output());

        return $path;
    }

    public function legacyAttendancePdf(LegacyMeetingAttendance $attendance, MeetingSchedule $schedule, string $payload)
    {
        $meeting = MeetingEvent::withoutGlobalScope('hotel')
            ->with(['hotel', 'booking.client', 'meetingRoom'])
            ->find($schedule->id);

        $hotel = $meeting?->hotel;

        return $this->loadTicketPdf('Meeting Attendance Participant QR', $hotel, $this->qrDataUri($payload), [
            'Purpose' => 'Legacy attendance participant credential',
            'Hotel' => $hotel?->name,
            'Client' => $meeting?->booking?->client?->company_name ?? $schedule->code_client,
            'Meeting' => $meeting?->event_name ?? $schedule->trx_number,
            'Schedule' => $meeting ? $this->scheduleText($meeting->start_at, $meeting->end_at) : trim($schedule->tgl_start.' '.$schedule->jam_mulai.' - '.$schedule->tgl_end.' '.$schedule->jam_selesai),
            'Room' => $meeting?->meetingRoom?->name ?? $schedule->room,
            'Participant' => $attendance->name,
            'Company' => $attendance->company,
            'Phone' => $attendance->phone_number,
            'Position' => $attendance->jabatan,
            'Attendance ID' => $attendance->id,
        ], 'This QR identifies the registered participant for attendance operations.');
    }

    public function filename(string $prefix, string $name): string
    {
        return Str::slug($prefix.'-'.$name).'.pdf';
    }

    private function loadTicketPdf(string $title, mixed $hotel, string $qrDataUri, array $details, string $note)
    {
        return Pdf::loadView('pdf.qr-ticket', [
            'title' => $title,
            'hotel' => $hotel,
            'logoDataUri' => $this->hotelLogo->dataUriFor($hotel),
            'qrDataUri' => $qrDataUri,
            'details' => array_filter($details, fn ($value) => filled($value)),
            'note' => $note,
            'generatedAt' => now(),
        ])->setPaper('a4');
    }

    private function qrDataUri(string $payload): string
    {
        $png = QrCode::format('png')
            ->size(320)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($payload);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private function scheduleText($start, $end): ?string
    {
        if (! $start && ! $end) {
            return null;
        }

        return trim(($start?->format('d M Y H:i') ?? '-').' - '.($end?->format('d M Y H:i') ?? '-'));
    }
}
