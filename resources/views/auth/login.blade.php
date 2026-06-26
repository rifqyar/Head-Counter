<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseurl" content="{{asset('')}}">

    <title>{{ 'Head Counter App | Login Page' }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/icon/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- CSS -->
    @include('includes.style')
    <style>
        html,
        body {
            min-height: 100%;
        }

        body {
            background: #101820;
        }

        .hc-login {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background-image:
                linear-gradient(90deg, rgba(16, 24, 32, .82) 0%, rgba(16, 24, 32, .58) 42%, rgba(16, 24, 32, .2) 100%),
                url('{{ asset('assets/images/background/login-register.jpg') }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .hc-login::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 18% 18%, rgba(38, 198, 218, .22), transparent 34%);
            pointer-events: none;
        }

        .hc-login-shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 48px;
        }

        .hc-login-content {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: 48px;
            align-items: center;
        }

        .hc-login-copy {
            color: #fff;
            max-width: 620px;
        }

        .hc-login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 6px;
            background: rgba(255, 255, 255, .1);
            color: #dff8ff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .hc-login-copy h1 {
            color: #fff;
            font-size: 44px;
            line-height: 1.08;
            margin-bottom: 16px;
            font-weight: 800;
        }

        .hc-login-copy p {
            color: rgba(255, 255, 255, .82);
            font-size: 17px;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .hc-login-card {
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 8px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
            backdrop-filter: blur(14px);
        }

        .hc-login-card .card-body {
            padding: 34px;
        }

        .hc-login-logo {
            max-width: 188px;
            max-height: 70px;
            object-fit: contain;
            margin-bottom: 24px;
        }

        .hc-login-card h2 {
            color: #263238;
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .hc-login-card .text-muted {
            color: #607d8b !important;
        }

        .hc-login-card label {
            color: #37474f;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .hc-login-card .form-control {
            height: 48px;
            border-color: #d8e0e5;
            border-radius: 6px;
            padding-left: 14px;
            font-size: 15px;
            background: #fff;
        }

        .hc-login-card .form-control:focus {
            border-color: #26c6da;
            box-shadow: 0 0 0 .2rem rgba(38, 198, 218, .16);
        }

        .hc-login-submit {
            height: 48px;
            border-radius: 6px;
            background: #0f766e;
            border-color: #0f766e;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .hc-login-submit:hover,
        .hc-login-submit:focus {
            background: #115e59;
            border-color: #115e59;
        }

        .hc-login-footnote {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #edf1f4;
            color: #78909c;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .hc-login {
                background-image:
                    linear-gradient(180deg, rgba(16, 24, 32, .78) 0%, rgba(16, 24, 32, .46) 100%),
                    url('{{ asset('assets/images/background/login-register.jpg') }}');
                background-position: center top;
            }

            .hc-login-shell {
                padding: 28px;
            }

            .hc-login-content {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .hc-login-copy {
                max-width: 720px;
            }

            .hc-login-copy h1 {
                font-size: 34px;
            }

            .hc-login-card {
                max-width: 460px;
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .hc-login-shell {
                align-items: flex-start;
                padding: 18px;
            }

            .hc-login-copy h1 {
                font-size: 27px;
            }

            .hc-login-copy p {
                font-size: 14px;
            }

            .hc-login-card .card-body {
                padding: 24px 20px;
            }

            .hc-login-logo {
                max-width: 150px;
                margin-bottom: 18px;
            }
        }
    </style>
</head>
<body>
    {{-- <div id="app">
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
                    </div>
                    <!--end col-->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card mb-0" style="opacity: 0.9;">
                            <div class="card-body p-3 p-sm-5 m-lg-2">
                                <div class="text-center">
                                    <img width="25%" src="{{ asset('assets/images/logo-sm.png') }}" alt="Logo EDII" style="border-radius: 1rem">
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
    </div> --}}

    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <section id="wrapper" class="hc-login">
        <div class="hc-login-shell">
            <div class="hc-login-content">
                <div class="hc-login-copy">
                    <div class="hc-login-eyebrow">
                        <i class="mdi mdi-qrcode-scan"></i>
                        head counter
                    </div>
                    <h1>Meeting attendance and entitlement scanning in one hotel-ready workspace.</h1>
                    <p>Sign in to manage bookings, rooms, participants, QR credentials, redemptions, and operational reporting across authorized hotel tenants.</p>
                </div>

                <div class="hc-login-card card">
                    <div class="card-body">
                        <form class="form-horizontal" id="loginform" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="text-center">
                                <img src="{{ asset('images/logo-full.png') }}" alt="Head Counter" class="hc-login-logo">
                                <h2>Welcome back</h2>
                                <p class="text-muted mb-4">Use your hotel operations account to continue.</p>
                            </div>

                            <div class="form-group">
                                <label for="username">Username</label>
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus placeholder="Enter username">

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <button class="btn btn-info btn-block text-uppercase waves-effect waves-light hc-login-submit mt-4" type="submit">Log In</button>

                            <div class="hc-login-footnote text-center">
                                Access is restricted to authorized hotel and platform users.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{asset('assets/plugins/popper/popper.min.js')}}"></script>
    <script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{asset('assets/js/jquery.slimscroll.js')}}"></script>
    <!--Wave Effects -->
    <script src="{{asset('assets/js/waves.js')}}"></script>
    <!--Menu sidebar -->
    <script src="{{asset('assets/js/sidebarmenu.js')}}"></script>
    <!--stickey kit -->
    <script src="{{asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js')}}"></script>
    <script src="{{asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
    <!--Custom JavaScript -->
    <script src="{{asset('assets/js/custom.min.js')}}"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="{{asset('assets/plugins/styleswitcher/jQuery.style.switcher.js')}}"></script>
</body>
</html>
