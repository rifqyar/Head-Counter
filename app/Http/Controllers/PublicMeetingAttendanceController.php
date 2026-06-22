<?php

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Domain\QRCode\MeetingQRService;
use App\Domain\QRCode\QrPdfService;
use App\Exceptions\DomainException;
use App\Http\Requests\PublicMeetingRegistrationRequest;

class PublicMeetingAttendanceController extends Controller
{
    public function show(string $token, MeetingQRService $meetingQRService)
    {
        [$valid, $meeting, $error] = $meetingQRService->validate($token);

        if (! $valid) {
            return response()->view('domain.attendance.invalid', ['message' => $error], 404);
        }

        $meeting->loadMissing(['hotel', 'meetingRoom']);

        return view('domain.attendance.register', compact('meeting', 'token'));
    }

    public function register(string $token, PublicMeetingRegistrationRequest $request, MeetingQRService $meetingQRService, RegisterParticipantAction $action, QrPdfService $qrPdfService)
    {
        [$valid, $meeting, $error] = $meetingQRService->validate($token);

        if (! $valid) {
            return response()->view('domain.attendance.invalid', ['message' => $error], 422);
        }

        $meeting->loadMissing(['hotel', 'meetingRoom']);

        try {
            $result = $action->executeWithQr($meeting, array_merge($request->validated(), ['registration_source' => 'MEETING_QR']));
        } catch (DomainException $exception) {
            return back()->withErrors(['registration' => $exception->getMessage()])->withInput();
        }

        $participant = $result['participant']->loadMissing(['hotel', 'meetingEvent.meetingRoom']);

        return view('domain.attendance.qr-issued', [
            'meeting' => $meeting,
            'participant' => $participant,
            'participantQrUrl' => $result['participant_qr_url'],
            'participantQrToken' => $result['participant_qr_token'],
            'participantQrPdfDataUri' => 'data:application/pdf;base64,'.base64_encode(
                $qrPdfService->participantPdf($participant, $result['participant_qr_url'])->output()
            ),
            'participantQrPdfName' => $qrPdfService->filename('participant-qr', $participant->full_name),
        ]);
    }
}
