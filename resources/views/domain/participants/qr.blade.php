@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Participant QR Administration</h4>
            <div class="text-muted">{{ $participant->full_name }} - {{ $participant->participant_number }}</div>
        </div>
        <a href="{{ route('participants.show', $participant) }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if ($issued && $issuedSvg)
        <div class="alert alert-warning">
            This QR is displayed once. Download or print it now; old QR images cannot be reconstructed because raw tokens are never stored.
        </div>
        <div class="row mb-4">
            <div class="col-md-4">{!! $issuedSvg !!}</div>
            <div class="col-md-8">
                <p><strong>Raw token:</strong></p>
                <textarea class="form-control mb-2" rows="3" readonly>{{ $issued['token'] }}</textarea>
                <p><strong>Scanner URL:</strong></p>
                <textarea class="form-control mb-3" rows="2" readonly>{{ $issued['url'] }}</textarea>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <h5>Participant</h5>
            <table class="table table-sm">
                <tr><th>Name</th><td>{{ $participant->full_name }}</td></tr>
                <tr><th>Number</th><td>{{ $participant->participant_number }}</td></tr>
                <tr><th>Meeting</th><td>{{ $participant->meetingEvent?->event_name }}</td></tr>
                <tr><th>Hotel</th><td>{{ $participant->hotel?->name }}</td></tr>
                <tr><th>Status</th><td>{{ $participant->status->value ?? $participant->status }}</td></tr>
            </table>

            @php($active = $participant->qrCredentials->firstWhere('status', \App\Enums\QRCredentialStatus::ACTIVE))
            <h5>Current Credential</h5>
            <table class="table table-sm">
                <tr><th>Status</th><td>{{ $active ? ($active->status->value ?? $active->status) : 'None' }}</td></tr>
                <tr><th>Token last four</th><td>{{ $active?->token_last_four ?? '-' }}</td></tr>
                <tr><th>Issued</th><td>{{ $active?->issued_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Expires</th><td>{{ $active?->expires_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Revoked</th><td>{{ $active?->revoked_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Revoked by</th><td>{{ $active?->revoked_by ?? '-' }}</td></tr>
            </table>

            @if (! $active)
                <form method="POST" action="{{ route('participants.qr.generate', $participant) }}">@csrf<button class="btn btn-success">Generate QR</button></form>
            @else
                <form method="POST" action="{{ route('participants.qr.rotate', $participant) }}" class="d-inline" onsubmit="return confirm('Rotate this QR and invalidate the previous credential?')">@csrf<input type="hidden" name="confirm" value="1"><button class="btn btn-warning">Rotate QR</button></form>
                <form method="POST" action="{{ route('participants.qr.revoke', $participant) }}" class="d-inline" onsubmit="return confirm('Revoke this QR credential?')">@csrf<input type="hidden" name="confirm" value="1"><button class="btn btn-outline-danger">Revoke QR</button></form>
            @endif
        </div>
        <div class="col-lg-7">
            <h5>Lifecycle History</h5>
            <table class="table table-striped">
                <thead><tr><th>Status</th><th>Last four</th><th>Issued</th><th>Expires</th><th>Revoked</th></tr></thead>
                <tbody>
                @forelse ($participant->qrCredentials as $credential)
                    <tr>
                        <td>{{ $credential->status->value ?? $credential->status }}</td>
                        <td>{{ $credential->token_last_four }}</td>
                        <td>{{ $credential->issued_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $credential->expires_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>{{ $credential->revoked_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No QR credential history.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
