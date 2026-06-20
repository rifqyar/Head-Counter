<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Participant QR Issued</title>
</head>
<body class="bg-light">
<main class="container py-4 text-center" style="max-width: 680px">
    <div class="alert alert-success text-left">
        <h1 class="h4">Registration complete</h1>
        <p class="mb-0">{{ $participant->full_name }} is registered for {{ $meeting->event_name }}.</p>
    </div>
    <div class="card card-body">
        <img alt="Participant QR" class="mx-auto img-fluid" style="max-width: 280px" src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(280)->generate($participantQrUrl)) }}">
        <p class="mt-3 small text-muted">This QR can only be shown now. Save or print it from this page.</p>
        <code class="small">{{ $participantQrUrl }}</code>
    </div>
</main>
</body>
</html>
