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
                        <form class="form form-vertical" action="{{route('meeting-attendance.store')}}" method="POST">
                            <input type="hidden" class="form-input" name="trx_number" value="{{$id}}">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="company">Nama Perusahaan</label>
                                            <input type="text" id="companyName" class="form-input form-control required"
                                                name="company" placeholder="Company Name">
                                        </div>
                                    </div>
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

                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" id="btn-save" class="btn btn-primary me-1 mb-1">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </section>

            @include('includes.footer')
        </div>
    </div>

    @include('includes.script')
    <script type="text/javascript" src="{{ asset('js/module/transaction/attendance.js') }}"></script>
</body>

</html>
