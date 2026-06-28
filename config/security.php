<?php

return [
    'headers' => [
        'csp' => env('SECURITY_CSP', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net; img-src 'self' data: blob:; connect-src 'self'; media-src 'self' blob:; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'"),
    ],
];
