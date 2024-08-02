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

            <section class="section row justify-content-center align-items-center vh-100">
                <div class="col-12">
                    <div class="d-flex justify-content-center align-items-center">
                        <lottie-player src="{{ asset('assets/invalid.json') }}" background="transparent" speed="1"
                            style="width: 150px; height: 150px;" loop autoplay></lottie-player>
                    </div>
                </div>
                <div class="col-12">
                    <h4 class="text-center">Mohon Maaf QR Code ini sudah kadaluarsa</h4>
                </div>
            </section>

            @include('includes.footer')
        </div>
    </div>

    @include('includes.script')
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</body>

</html>
