<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Participant</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/icon/favicon.ico') }}">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/wizard/steps.css') }}" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            color: #263238;
            min-height: 100vh;
        }

        .attendance-shell {
            max-width: 860px;
        }

        .attendance-logo {
            max-height: 58px;
            max-width: 210px;
            object-fit: contain;
        }

        .attendance-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        }

        .attendance-summary {
            background: linear-gradient(135deg, #0f766e, #155e75);
            border-radius: 8px 8px 0 0;
            color: #fff;
            padding: 22px 24px;
        }

        .attendance-summary .text-muted {
            color: rgba(255, 255, 255, .78) !important;
        }

        .wizard-content .wizard > .steps > ul > li {
            width: 33.3333%;
        }

        .wizard-content .wizard > .content {
            background: #fff;
            min-height: 260px;
        }

        .wizard-content .wizard > .actions {
            padding: 0 24px 24px;
        }

        .fallback-submit {
            display: block;
        }

        .wizard-ready .fallback-submit {
            display: none;
        }

        @media (max-width: 575.98px) {
            .attendance-summary {
                padding: 18px;
            }

            .wizard-content .wizard > .steps > ul > li {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<main class="container attendance-shell py-4 py-md-5">
    <div class="text-center mb-4">
        <img src="{{ app(\App\Support\Branding\HotelLogo::class)->assetFor($meeting->hotel) }}" alt="{{ $meeting->hotel?->name ?? 'Hotel' }} logo" class="attendance-logo">
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Registration could not be completed.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="card attendance-card wizard-content">
        <div class="attendance-summary">
            <div class="text-uppercase small mb-2">Meeting Registration</div>
            <h1 class="h3 mb-2">{{ $meeting->event_name }}</h1>
            <div class="row">
                <div class="col-md-6">
                    <div class="text-muted">Schedule</div>
                    <strong>{{ $meeting->event_date?->format('d M Y') }} · {{ $meeting->start_at?->format('H:i') }}-{{ $meeting->end_at?->format('H:i') }}</strong>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="text-muted">Room</div>
                    <strong>{{ $meeting->meetingRoom?->name ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('attendance.meeting.register', $token) }}" id="public-participant-wizard" class="p-4">
            @csrf

            <h3>Participant</h3>
            <section>
                <div class="form-group">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required>
                    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Company</label>
                    <input class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}">
                    @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </section>

            <h3>Identity</h3>
            <section>
                <div class="alert alert-info">
                    Provide email, phone, or identity reference so duplicate registration can be prevented.
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Identity Reference</label>
                    <input class="form-control @error('identity_reference') is-invalid @enderror" name="identity_reference" value="{{ old('identity_reference') }}">
                    @error('identity_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </section>

            <h3>Confirm</h3>
            <section>
                <div class="alert alert-light border">
                    Please confirm your details before generating the participant QR. The QR is shown once after registration.
                </div>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Meeting</dt>
                    <dd class="col-sm-8">{{ $meeting->event_name }}</dd>
                    <dt class="col-sm-4">Hotel</dt>
                    <dd class="col-sm-8">{{ $meeting->hotel?->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Schedule</dt>
                    <dd class="col-sm-8">{{ $meeting->event_date?->format('d M Y') }} · {{ $meeting->start_at?->format('H:i') }}-{{ $meeting->end_at?->format('H:i') }}</dd>
                    <dt class="col-sm-4">Room</dt>
                    <dd class="col-sm-8">{{ $meeting->meetingRoom?->name ?? '-' }}</dd>
                </dl>
            </section>

            <button class="btn btn-primary btn-block fallback-submit">Register and Get QR</button>
        </form>
    </div>
</main>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/wizard/jquery.steps.min.js') }}"></script>
<script src="{{ asset('assets/plugins/wizard/jquery.validate.min.js') }}"></script>
<script>
    $(function () {
        var form = $('#public-participant-wizard');

        if (!form.length || typeof $.fn.steps !== 'function') {
            return;
        }

        form.addClass('wizard-ready').steps({
            headerTag: 'h3',
            bodyTag: 'section',
            transitionEffect: 'fade',
            autoFocus: true,
            labels: {
                finish: 'Register and Get QR',
                next: 'Next',
                previous: 'Back'
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                if (currentIndex > newIndex) {
                    return true;
                }

                if ($.fn.validate) {
                    form.validate().settings.ignore = ':disabled,:hidden';
                    return form.valid();
                }

                return true;
            },
            onFinishing: function () {
                if ($.fn.validate) {
                    form.validate().settings.ignore = ':disabled';
                    return form.valid();
                }

                return true;
            },
            onFinished: function () {
                form.get(0).submit();
            }
        });
    });
</script>
</body>
</html>
