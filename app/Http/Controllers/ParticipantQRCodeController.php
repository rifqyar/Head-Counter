<?php

namespace App\Http\Controllers;

use App\Domain\Participant\Participant;
use App\Domain\QRCode\ParticipantQRCredential;
use App\Domain\QRCode\ParticipantQRService;
use App\Domain\QRCode\QrPdfService;
use App\Http\Requests\ConfirmParticipantQRRequest;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParticipantQRCodeController extends Controller
{
    public function show(Request $request, Participant $participant, AuditLogger $auditLogger, QrPdfService $qrPdfService)
    {
        $this->authorize('manageQr', $participant);
        $auditLogger->record('participant_qr.admin_viewed', $participant->hotel_id, $request->user()->id, $participant);

        $participant->load(['hotel', 'meetingEvent', 'activeQrCredential', 'qrCredentials' => fn ($query) => $query->orderByDesc('issued_at')]);
        $issued = session('participant_qr_issued');
        $issuedSvg = null;
        $issuedPdfDataUri = null;
        $issuedPdfName = null;

        if (($issued['participant_id'] ?? null) === $participant->id && ! empty($issued['url'])) {
            $issuedSvg = QrCode::format('svg')->size(260)->generate($issued['url']);
            $issuedPdfDataUri = 'data:application/pdf;base64,'.base64_encode(
                $qrPdfService->participantPdf($participant, $issued['url'])->output()
            );
            $issuedPdfName = $qrPdfService->filename('participant-qr', $participant->full_name);
        }

        return $this->viewOrRedirect($request, 'domain.participants.qr', compact('participant', 'issued', 'issuedSvg', 'issuedPdfDataUri', 'issuedPdfName'));
    }

    public function generate(Request $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('manageQr', $participant);

        if ($participant->activeQrCredential()->exists()) {
            return back()->withErrors(['qr' => 'This participant already has an active QR credential. Rotate it to issue a new code.']);
        }

        $issued = $service->generate($participant, $request->user()->id);

        return $this->redirectWithIssuedQr($participant, $issued, 'Participant QR generated.');
    }

    public function rotate(ConfirmParticipantQRRequest $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('manageQr', $participant);
        $request->validated();

        $issued = $service->generate($participant, $request->user()->id);

        return $this->redirectWithIssuedQr($participant, $issued, 'Participant QR rotated. The previous QR is no longer valid.');
    }

    public function revoke(ConfirmParticipantQRRequest $request, Participant $participant, ParticipantQRService $service)
    {
        $this->authorize('manageQr', $participant);
        $request->validated();

        $credential = $participant->activeQrCredential()->first();
        if (! $credential instanceof ParticipantQRCredential) {
            return back()->withErrors(['qr' => 'No active QR credential is available to revoke.']);
        }

        $service->revoke($credential, $request->user()->id);

        return redirect()->route('participants.qr.show', $participant)->with('status', 'Participant QR revoked.');
    }

    public function downloadActive(Request $request, Participant $participant)
    {
        $this->authorize('manageQr', $participant);

        $credential = $participant->activeQrCredential()->first();
        if (! $credential?->printable_path || ! Storage::disk('public')->exists($credential->printable_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($credential->printable_path, 'participant-qr-'.$participant->participant_number.'.pdf');
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
