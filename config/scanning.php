<?php

return [
    // Enable or disable server-side virus scanning of uploaded files.
    // This should be enabled only when a trusted scanner (clamd/clamscan) is
    // available on the host or via a sidecar and you accept the additional
    // operational risk of launching a subprocess.
    'virus' => [
        'enabled' => env('VIRUS_SCAN_ENABLED', false),
    ],
];
