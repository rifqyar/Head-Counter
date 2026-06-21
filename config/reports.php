<?php

return [
    'starting_soon_minutes' => env('REPORTS_STARTING_SOON_MINUTES', 60),
    'upcoming_hours' => env('REPORTS_UPCOMING_HOURS', 24),
    'recent_scanner_failure_hours' => env('REPORTS_RECENT_SCANNER_FAILURE_HOURS', 24),
    'export_expiration_days' => env('REPORTS_EXPORT_EXPIRATION_DAYS', 7),
    'sync_thresholds' => [
        'xlsx' => env('REPORTS_SYNC_XLSX_ROWS', 1000),
        'csv' => env('REPORTS_SYNC_CSV_ROWS', 5000),
        'pdf' => env('REPORTS_SYNC_PDF_ROWS', 250),
    ],
    'room_utilization_hours_per_day' => env('REPORTS_ROOM_UTILIZATION_HOURS_PER_DAY', 24),
];
