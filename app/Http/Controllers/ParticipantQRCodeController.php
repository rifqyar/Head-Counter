<?php

namespace App\Http\Controllers;

use App\Domain\Participant\Participant;
use App\Domain\QRCode\ParticipantQRCredential;
use App\Domain\QRCode\ParticipantQRService;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParticipantQRCodeController extends Controller
{
    public function show(Request $request, Participant $participant, AuditLogger $auditLogger)
    {
        $this->authorize('view', $participant);
        $auditLogger->record('participant_qr.admin_viewed', $participant->hotel_id, $request->user()->id, $participant);

        $participant->load(['hotel', 'meetingEvent', 'qrCredentials' => fn ($query) => $query->orderByDesc('issued_at')]);
        $issued = session('participant_qr_issued');
        $issuedSvg = null;

        if (($issued['participant_id'] ?? null) === $participant->id && ! empty($issued['url'])) {
            $issuedSvg = QrCode::format('svg')->size(260)->generate($issued['url']);
        }

        return view('domain.participants.qr', compact('participant', 'issued', 'issuedSvg'));
    }

    public function generate(Request $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('update', $participant);

        if ($participant->activeQrCredential()->exists()) {
            return back()->withErrors(['qr' => 'This participant already has an active QR credential. Rotate it to issue a new code.']);
        }

        $issued = $service->generate($participant, $request->user()->id);

        return $this->redirectWithIssuedQr($participant, $issued, 'Participant QR generated.');
    }

    public function rotate(Request $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('update', $participant);
        $request->validate(['confirm' => ['accepted']]);

        $issued = $service->generate($participant, $request->user()->id);

        return $this->redirectWithIssuedQr($participant, $issued, 'Participant QR rotated. The previous QR is no longer valid.');
    }

    public function revoke(Request $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('update', $participant);
        $request->validate(['confirm' => ['accepted']]);

        $credential = $participant->activeQrCredential()->first();
        if (! $credential instanceof ParticipantQRCredential) {
            return back()->withErrors(['qr' => 'No active QR credential is available to revoke.']);
        }

        $service->revoke($credential, $request->user()->id);

        return redirect()->route('participants.qr.show', $participant)->with('status', 'Participant QR revoked.');
    }

    private function redirectWithIssuedQr(Participant $participant, array $issued, string $message)
    {
        return redirect()
            ->route('participants.qr.show', $participant)
            ->with('status', $message.' Raw QR is shown once on this page.')
            ->with('participant_qr_issued', [
                'participant_id' => $participant->id,
                'credential_id' => $issued['credential']->id,
                'token' => $issued['token'],
                'url' => $issued['url'],
            ]);
    }
}
