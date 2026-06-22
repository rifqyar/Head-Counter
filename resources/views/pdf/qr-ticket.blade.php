<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #1f2933;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
        }
        .page {
            padding: 30px;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 24px;
            padding-bottom: 16px;
        }
        .logo {
            max-height: 58px;
            max-width: 190px;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 12px 0 4px;
        }
        .hotel {
            color: #4b5563;
            font-size: 13px;
        }
        .content {
            width: 100%;
        }
        .qr-box {
            border: 1px solid #d1d5db;
            padding: 18px;
            text-align: center;
            width: 255px;
        }
        .qr-box img {
            height: 220px;
            width: 220px;
        }
        .details {
            padding-left: 24px;
            vertical-align: top;
        }
        table.info {
            border-collapse: collapse;
            width: 100%;
        }
        table.info th,
        table.info td {
            border-bottom: 1px solid #edf2f7;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }
        table.info th {
            color: #4b5563;
            font-weight: 700;
            width: 34%;
        }
        .note {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            color: #334155;
            margin-top: 24px;
            padding: 12px 14px;
        }
        .footer {
            bottom: 24px;
            color: #6b7280;
            font-size: 10px;
            left: 30px;
            position: fixed;
            right: 30px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" class="logo" alt="Hotel Logo">
            @endif
            <div class="title">{{ $title }}</div>
            <div class="hotel">{{ $hotel?->name ?? 'Head Counter App' }}</div>
        </div>

        <table class="content">
            <tr>
                <td class="qr-box">
                    <img src="{{ $qrDataUri }}" alt="QR Code">
                </td>
                <td class="details">
                    <table class="info">
                        @foreach ($details as $label => $value)
                            <tr>
                                <th>{{ $label }}</th>
                                <td>{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        <div class="note">{{ $note }}</div>
    </div>

    <div class="footer">
        Generated at {{ $generatedAt->format('d M Y H:i') }}. QR tokens are stored as hashes; keep this document secure.
    </div>
</body>
</html>
