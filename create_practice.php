<?php
use Illuminate\\Support\\Carbon;

require '/var/www/app.letstalkspanish.io/vendor/autoload.php';
 = require '/var/www/app.letstalkspanish.io/bootstrap/app.php';
->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

 = 1;
 = Carbon::now()->addDays(2)->setTime(16, 0, 0);
 = App\\Models\\DiscordPractice::create([
    'lesson_id' => ,
    'title' => 'QA Planner Test',
    'description' => 'Prueba automatica planner',
    'type' => 'cohort',
    'cohort_label' => 'QA Cohort',
    'practice_package_id' => null,
    'start_at' => ,
    'end_at' => ->copy()->addMinutes(60),
    'duration_minutes' => 60,
    'capacity' => 8,
    'discord_channel_url' => 'https://discord.gg/example',
    'created_by' => 2,
    'requires_package' => false,
]);

echo 'Created practice ID: '.->id.PHP_EOL;

