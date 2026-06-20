<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Register Participant</title>
</head>
<body class="bg-light">
<main class="container py-4" style="max-width: 680px">
    <h1 class="h4 mb-1">{{ $meeting->event_name }}</h1>
    <p class="text-muted">{{ $meeting->event_date?->format('d M Y') }} · {{ $meeting->start_at?->format('H:i') }}-{{ $meeting->end_at?->format('H:i') }}</p>
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('attendance.meeting.register', $token) }}" class="card card-body">
        @csrf
        <div class="form-group"><label>Full Name</label><input class="form-control" name="full_name" value="{{ old('full_name') }}" required></div>
        <div class="form-group"><label>Company</label><input class="form-control" name="company_name" value="{{ old('company_name') }}"></div>
        <div class="form-group"><label>Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div>
        <div class="form-group"><label>Phone</label><input class="form-control" name="phone" value="{{ old('phone') }}"></div>
        <div class="form-group"><label>Identity Reference</label><input class="form-control" name="identity_reference" value="{{ old('identity_reference') }}"></div>
        <button class="btn btn-primary btn-block">Register and Get QR</button>
    </form>
</main>
</body>
</html>
