<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <title>Form Attendance Meeting</title>
    @include('includes.style')
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseurl" content="{{ asset('') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/icon/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main/app-dark.css') }}">
</head>

<body>
    <div id="main" class="layout-navbar">
        <div id="main-content">
            <div class="loading" style="display: none">
                <div class="spinner-wrapper">
                    <span class="spinner-text">Loading...</span>
                    <span class="spinner"></span>
                </div>
            </div>

            <section class="section row">
                <div class="card">
                    <div class="card-header">
                        <h2>Form Meeting Attendance</h2>
                    </div>
                    <div class="card-body">
                        <form class="form form-vertical attendance-wizard wizard-circle" id="meeting-attendance-form" action="{{route('meeting-attendance.store')}}" method="POST">
                            <input type="hidden" class="form-input" name="trx_number" value="{{$id}}">
                            <input type="hidden" class="form-input" name="qr_code" value="{{ $qrCode ?? '' }}">
                            @csrf
                            <h6>Company</h6>
                            <section>
                                <div class="row">
                                    <div class="col-md-12 col-12">
                                        <div class="form-group">
                                            <label for="company">Nama Perusahaan</label>
                                            <input type="text" id="companyName" class="form-input form-control required"
                                                name="company" placeholder="Company Name">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <h6>Participant</h6>
                            <section>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="name">Nama Lengkap</label>
                                            <input type="text" id="contactPerson" class="form-input form-control required"
                                                name="name" placeholder="Nama Lengkap">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="jabatan">Jabatan</label>
                                            <input type="text" id="jabatan" class="form-input form-control required"
                                                name="jabatan" placeholder="jabatan">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="phone_number">Nomor Telepon</label>
                                            <input type="text" id="phone_number" class="form-input form-control required"
                                                name="phone_number" placeholder="Nomor Telepon">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <h6>Confirmation</h6>
                            <section>
                                <div class="alert alert-info">
                                    Please confirm your attendance information. A personal QR code will be downloaded after submission.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Meeting:</strong> {{ $id }}</p>
                                        <p><strong>Company:</strong> <span id="attendance-preview-company">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> <span id="attendance-preview-name">-</span></p>
                                        <p><strong>Phone:</strong> <span id="attendance-preview-phone">-</span></p>
                                    </div>
                                </div>
                            </section>

                            <div class="attendance-form-actions">
                                <button type="submit" class="btn btn-primary">Submit Attendance</button>
                            </div>
                        </form>
                    </div>

                </div>
            </section>

            @include('includes.footer')
        </div>
    </div>

    @include('includes.script')
    <script>
        (function () {
            var form = $('#meeting-attendance-form');

            function updatePreview() {
                $('#attendance-preview-company').text($('input[name="company"]').val() || '-');
                $('#attendance-preview-name').text($('input[name="name"]').val() || '-');
                $('#attendance-preview-phone').text($('input[name="phone_number"]').val() || '-');
            }

            if (form.length && typeof $.fn.steps === 'function') {
                form.show().steps({
                    headerTag: 'h6',
                    bodyTag: 'section',
                    transitionEffect: 'fade',
                    autoFocus: true,
                    titleTemplate: '<span class="step">#index#</span> #title#',
                    labels: {
                        finish: 'Submit',
                        next: 'Next',
                        previous: 'Back'
                    },
                    onStepChanging: function (event, currentIndex, newIndex) {
                        updatePreview();
                        if (currentIndex > newIndex || typeof form.validate !== 'function') {
                            return true;
                        }

                        form.validate().settings.ignore = ':disabled,:hidden';
                        return form.valid();
                    },
                    onFinishing: function () {
                        updatePreview();
                        if (typeof form.validate === 'function') {
                            form.validate().settings.ignore = ':disabled';
                            return form.valid();
                        }

                        return true;
                    },
                    onFinished: function () {
                        form.get(0).submit();
                    }
                });

                if (typeof form.validate === 'function') {
                    form.validate({
                        ignore: '',
                        errorClass: 'text-danger',
                        errorPlacement: function (error, element) {
                            error.insertAfter(element);
                        }
                    });
                }

                form.find('.attendance-form-actions').hide();
            }

            form.on('input', 'input', updatePreview);
            updatePreview();
        })();
    </script>
</body>

</html>
