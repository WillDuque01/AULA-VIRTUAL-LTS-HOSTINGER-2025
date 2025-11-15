<?php

namespace App\Support\Analytics;

use App\Models\VideoHeatmapSegment;
use App\Models\VideoProgress;

class VideoHeatmapRecorder
{
    public function record(VideoProgress $progress, int $lastSecond): void
    {
        if (! $progress->lesson_id) {
            return;
        }

        $bucketSize = max(1, (int) config('player.heatmap_bucket_seconds', 15));
        $currentBucket = intdiv($lastSecond, $bucketSize);
        $previousBucket = $progress->last_recorded_bucket ?? -1;

        if ($currentBucket <= $previousBucket) {
            return;
        }

        foreach (range($previousBucket + 1, $currentBucket) as $bucket) {
            $segment = VideoHeatmapSegment::firstOrCreate([
                'lesson_id' => $progress->lesson_id,
                'bucket' => $bucket,
            ]);

            $segment->increment('reach_count');
        }

        $progress->last_recorded_bucket = $currentBucket;
        $progress->save();
    }
}

