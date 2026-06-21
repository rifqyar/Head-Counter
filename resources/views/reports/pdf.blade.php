<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 4px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>{{ \App\Enums\ReportType::tryFrom($title)?->label() ?? $title }}</h2>
    <p>Hotel: {{ $filter->hotel?->name ?? 'All authorized hotels' }} | Generated: {{ now()->timezone($filter->timezone)->format('Y-m-d H:i') }} | Timezone: {{ $filter->timezone }}</p>
    <table>
        <thead><tr>@foreach ($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
