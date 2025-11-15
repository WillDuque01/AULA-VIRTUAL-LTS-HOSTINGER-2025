<?php

return [
    'heatmap_bucket_seconds' => (int) env('PLAYER_HEATMAP_BUCKET_SECONDS', 15),
    'heatmap_limit_buckets' => (int) env('PLAYER_HEATMAP_LIMIT_BUCKETS', 600),
];

