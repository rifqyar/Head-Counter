<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Participant QR Issued</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/icon/favicon.ico') }}">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            color: #263238;
            min-height: 100vh;
        }

        .issued-shell {
            max-width: 920px;
        }

        .issued-logo {
            max-height: 58px;
            max-width: 210px;
            object-fit: contain;
        }

        .issued-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .issued-header {
            background: linear-gradient(135deg, #0f766e, #155e75);
            color: #fff;
            padding: 22px 24px;
        }

        .issued-header .text-muted {
            color: rgba(255, 255, 255, .78) !important;
        }

        .qr-frame {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: inline-block;
            padding: 14px;
        }

        .qr-frame img {
            max-width: 280px;
            width: 100%;
        }

        .detail-table th {
            color: #54667a;
            width: 165px;
        }

        @media (max-width: 575.98px) {
            .issued-header {
                padding: 18px;
            }

            .detail-table th,
            .detail-table td {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<main class="container issued-shell py-4 py-md-5">
    <div class="text-center mb-4">
        <img src="{{ app(\App\Support\Branding\HotelLogo::class)->assetFor($meeting->hotel) }}" alt="{{ $meeting->hotel?->name ?? 'Hotel' }} logo" class="issued-logo">
    </div>

    <div class="issued-card bg-white">
        <div class="issued-header">
            <div class="text-uppercase small mb-2">Registration Complete</div>
            <h1 class="h3 mb-2">{{ $participant->full_name }}</h1>
            <p class="mb-0 text-muted">Registered for {{ $meeting->event_name }}. Save or download this participant QR now.</p>
        </div>

        <div class="card-body p-4">
            <div class="row align-items-start">
                <div class="col-lg-5 text-center mb-4 mb-lg-0">
                    <div class="qr-frame">
                        <img alt="Participant QR" src="data:image/png;base64,{{ base64_encode(QrCode::format('png')->size(280)->margin(2)->generate($participantQrUrl)) }}">
                    </div>
                    <a class="btn btn-primary btn-block mt-3" href="{{ $participantQrPdfDataUri }}" download="{{ $participantQrPdfName }}">Download Participant QR PDF</a>
                </div>
                <div class="col-lg-7">
                    <div class="alert alert-info">
                        This QR belongs to one participant. It is shown once after registration; the active credential can later be reprinted by an authorized operator.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm detail-table">
                            <tr><th>Hotel</th><td>{{ $participant->hotel?->name ?? $meeting->hotel?->name ?? '-' }}</td></tr>
                            <tr><th>Participant</th><td>{{ $participant->full_name }}</td></tr>
                            <tr><th>Participant No.</th><td>{{ $participant->participant_number }}</td></tr>
                            <tr><th>Meeting</th><td>{{ $meeting->event_name }}</td></tr>
                            <tr><th>Schedule</th><td>{{ $meeting->start_at?->format('d M Y H:i') }} - {{ $meeting->end_at?->format('d M Y H:i') }}</td></tr>
                            <tr><th>Room</th><td>{{ $meeting->meetingRoom?->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
