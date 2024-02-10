<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseurl" content="{{ asset('') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/icon/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="stylesheet" href="{{ asset('/') }}assets/plugins/datatables/media/css/dataTables.bootstrap4.min.css">

    <!-- Styles -->
    @stack('before-style')
    @include('includes.style')
    @stack('after-style')
</head>

<body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2"
                stroke-miterlimit="10" />
        </svg>
    </div>

    <div id="main-wrapper">
        @include('includes.navbar')

        @include('includes.sidebar')

        <div id="main-content" class="page-wrapper">

            @yield('content')
            @include('includes.footer')
        </div>
    </div>

    @stack('before-script')
    @include('includes.script')
    @stack('after-script')

    @if (session()->has('Redirect'))
        <script>
            // Fungsi untuk menjalankan renderView secara otomatis
            function autoRenderView() {
                renderView(`/{!! session('Redirect') !!}`);
            }

            // Panggil fungsi untuk menjalankan renderView setelah halaman sepenuhnya dimuat
            document.addEventListener('DOMContentLoaded', autoRenderView);
        </script>
    @endif
</body>

</html>
