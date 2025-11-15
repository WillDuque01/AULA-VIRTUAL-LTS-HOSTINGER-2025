<?php

return [
    'auto_issue' => env('CERTIFICATES_AUTO_ISSUE', true),
    'completion_threshold' => (int) env('CERTIFICATES_THRESHOLD', 90),
];


