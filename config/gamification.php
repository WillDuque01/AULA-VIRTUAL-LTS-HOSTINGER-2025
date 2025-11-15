<?php

return [
    'video_completion_points' => (int) env('GAMIFICATION_VIDEO_POINTS', 50),
    'streak_window_hours' => (int) env('GAMIFICATION_STREAK_WINDOW', 36),
    'milestones' => [
        5 => '🔥 Racha x5',
        10 => '⚡ Maestro del progreso',
        20 => '🏆 Leyenda del aula',
    ],
];


