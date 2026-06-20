<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Invalid QR</title>
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="alert alert-danger">
        <h1 class="h4">QR unavailable</h1>
        <p class="mb-0">{{ $message ?? 'This QR code cannot be used.' }}</p>
    </div>
</main>
</body>
</html>
