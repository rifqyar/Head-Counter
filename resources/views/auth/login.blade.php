<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseurl" content="{{asset('')}}">

    <title>{{ 'Head Counter App | Login Page' }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/icon/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/main/app-dark.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/main/app.css')}}">
    <link rel="stylesheet" href="{{asset('assets/extensions/loading/loading.css')}}">
</head>
<body>
    <div id="app">
        <div class="loading" style="display: none">
            <div class="spinner-wrapper">
              <span class="spinner-text">Loading</span>
              <span class="spinner"></span>
            </div>
          </div>
        <section class="auth-bg-cover min-vh-100 p-4 p-lg-5 d-flex align-items-center justify-content-center">
            <div class="bg-overlay"></div>
            <div class="container-fluid px-0">
                <div class="row g-0">
                    <div class="col-xl-8 col-lg-6">
                        {{-- <div class="h-100 mb-0 p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <img src="assets/images/provos_light.png" alt="" height="32" />
                                </div>
                            </div>
                        </div> --}}
                    </div>
                    <!--end col-->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card mb-0" style="opacity: 0.9;">
                            <div class="card-body p-3 p-sm-5 m-lg-2">
                                <div class="text-center">
                                    <img width="25%" src="{{ asset('assets/images/logo/logo-sm.png') }}" alt="Logo EDII" style="border-radius: 1rem">
                                    <h5 class="text-primary fs-22 mt-3">Welcome Back !</h5>
                                    <p class="text-muted">Sign in to continue to Head Counter App.</p>
                                </div>
                                <div class="p-2 mt-5">
                                    <form action="{{ route('login') }}" method="post">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                            @error('username')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Password</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                                {{-- <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                                                    <i class="bi bi-eye" id="eye"></i>
                                                </button> --}}
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-primary w-100" type="submit">Sign In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end conatiner-->
        </section>
    </div>

    {{-- Script --}}

    <script src="{{asset('assets/js/bootstrap.js')}}"></script>
    <script src="{{asset('assets/extensions/jquery/jquery.js')}}"></script>
    <script src="{{asset('js/modul/auth/login.js')}}"></script>
    <script src="{{asset('js/core/core.js')}}"></script>
</body>
</html>
