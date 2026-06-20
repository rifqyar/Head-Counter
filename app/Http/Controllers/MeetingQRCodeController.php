<?php

namespace App\Http\Controllers;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\QRCode\MeetingQRService;
use Illuminate\Support\Facades\Storage;

class MeetingQRCodeController extends Controller
{
    public function generate(MeetingEvent $meeting, MeetingQRService $service)
    {
        $this->authorize('update', $meeting);
        $result = $service->generate($meeting, auth()->id());

        return back()->with('status', 'Meeting QR generated. Raw token is only available now: '.$result['url']);
    }

    public function regenerate(MeetingEvent $meeting, MeetingQRService $service)
    {
        $this->authorize('update', $meeting);
        $result = $service->regenerate($meeting, auth()->id());

        return back()->with('status', 'Meeting QR regenerated. Raw token is only available now: '.$result['url']);
    }

    public function revoke(MeetingEvent $meeting, MeetingQRService $service)
    {
        $this->authorize('update', $meeting);
        $service->revoke($meeting, auth()->id());

        return back()->with('status', 'Meeting QR revoked.');
    }

    public function download(MeetingEvent $meeting, MeetingQRService $service)
    {
        $this->authorize('view', $meeting);

        if (! $meeting->meeting_qr_path || ! Storage::disk('public')->exists($meeting->meeting_qr_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($meeting->meeting_qr_path);
    }
}
