<?php

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Domain\QRCode\MeetingQRService;
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

        return view('domain.attendance.register', compact('meeting', 'token'));
    }

    public function register(string $token, PublicMeetingRegistrationRequest $request, MeetingQRService $meetingQRService, RegisterParticipantAction $action)
    {
        [$valid, $meeting, $error] = $meetingQRService->validate($token);

        if (! $valid) {
            return response()->view('domain.attendance.invalid', ['message' => $error], 422);
        }

        try {
            $result = $action->executeWithQr($meeting, array_merge($request->validated(), ['registration_source' => 'MEETING_QR']));
        } catch (DomainException $exception) {
            return back()->withErrors(['registration' => $exception->getMessage()])->withInput();
        }

        return view('domain.attendance.qr-issued', [
            'meeting' => $meeting,
            'participant' => $result['participant'],
            'participantQrUrl' => $result['participant_qr_url'],
            'participantQrToken' => $result['participant_qr_token'],
        ]);
    }
}
