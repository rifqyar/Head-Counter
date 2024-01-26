<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forbidden</title>


    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/icon/favicon.ico') }}">

    <style>
        #error {
            background-color: #ebf3ff;
            padding: 2rem 0;
            min-height: 100vh
        }

        #error .img-error {
            height: 435px;
            object-fit: contain;
            padding: 3rem 0
        }

        #error .error-title {
            font-size: 3rem;
            margin-top: 1rem
        }

        .img-error {
            {{-- border-radius: 30%; --}}
        }


        html[data-bs-theme=dark] #error {
            background-color: #151521
        }
    </style>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main/app-dark.css') }}">
</head>

<body>
    <script src="assets/static/js/initTheme.js"></script>
    <div id="error">
        <div class="error-page container">
            <div class="col-md-8 col-12 offset-md-2">
                <div class="text-center">
                    <img class="img-error" src="{{ asset('assets/images/samples/error-403.svg') }}" alt="Not Found" />
                    <h1 class="error-title">403</h1>
                    <p class="fs-5 text-gray-600">
                        Forbidden
                    </p>
                    <a href="{{route('dashboard.index')}}" class="btn btn-lg btn-outline-primary mt-3">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
