<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <title>@yield('title')</title>
    @stack('before-style')
    @include('includes.style')
    @stack('after-style')
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

    @include('includes.sidebar')
    <div id="main" class="layout-navbar">
        @include('includes.navbar')
        <div id="main-content">
            <div class="loading" style="display: none">
                <div class="spinner-wrapper">
                    <span class="spinner-text">Loading...</span>
                    <span class="spinner"></span>
                </div>
            </div>

            @yield('content')
            @include('includes.footer')
        </div>
    </div>

    @stack('before-script')
    @include('includes.script')
    @stack('after-script')

    <script src="{{ asset('js/core/table.js') }}"></script>
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
