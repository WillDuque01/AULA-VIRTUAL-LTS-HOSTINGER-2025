<?php

return [
    'title' => 'Help Center & Documentation',
    'view_link' => 'View documentation',
    'sections' => [
        'getting-started' => [
            'title' => 'Getting started',
            'content' => <<<'MD'
1. **Enable locales** under *Branding → Locales* so `/es/*` and `/en/*` routes stay in sync.
2. **Set your branding** (logo, palette, fonts) to align the public landing with the dashboards.
3. **Seed base roles**: at least one `admin`, `teacher_admin` and `student_paid` (use `AuditorProfilesSeeder` locally).
4. **Publish a landing** from the Page Builder and mark it as *home* to replace the temporary placeholder.
5. **Clear caches** (`php artisan optimize:clear`) and ship compiled assets (`npm run build`) before deploying.
MD,
        ],
        'course-builder' => [
            'title' => 'Course Builder',
            'content' => <<<'MD'
* Drag chapters and lessons with the Alpine `courseBuilderDnD` module (keyboard-accessible).
* Use the focus chips to block progress, schedule releases or attach Discord practices.
* Each lesson supports CTA, badges, declared duration and prerequisites from the lateral inspector.
* When done, trigger **Save order** (event `builder-reorder`) to persist the structure.
MD,
        ],
        'discord-practices' => [
            'title' => 'Discord practices',
            'content' => <<<'MD'
* The teacher planner lets you duplicate slots, move them across cohorts and sync reminders.
* Students see a responsive grid with clear states (`Available`, `Waitlist`, `Pack required`).
* Packs originate in `Market → Practice Packages`; every purchase emits Discord + WhatsApp notifications.
* Keep at least **two weeks** of published practices to avoid gaps in the agenda.
MD,
        ],
        'dataporter-hub' => [
            'title' => 'DataPorter & automations',
            'content' => <<<'MD'
* DataPorter centralizes exports for courses, practices and certificates.
* Ensure `DATAPORTER_API_KEY` and `DATAPORTER_ENDPOINT` exist in `.env` and on the VPS.
* Schedule Supervisor jobs (`dataporter:sync`) so dashboards remain fresh without manual runs.
MD,
        ],
        'player-signals' => [
            'title' => 'Player telemetry',
            'content' => <<<'MD'
* `/api/player/events` enforces the `player-events` rate limiter and dispatches `RecordPlayerEventJob`.
* Use `TelemetryQueueTest` as a template to simulate `play`, `pause` or `complete`.
* Set `TelemetryRecorder::$useQueue = true` in production to avoid blocking Livewire threads.
MD,
        ],
        'planner-operations' => [
            'title' => 'Planner operations',
            'content' => <<<'MD'
1. Publish a `PracticePackage` (`published` status) and link it to a cohort.
2. From the admin planner you can label slots as **Discord / Zoom / Meet** per need.
3. Every practice triggers webhooks to Discord, posting the confirmed student list to the teacher channel.
MD,
        ],
        'student-panel' => [
            'title' => 'Student dashboard',
            'content' => <<<'MD'
* The top summary reflects progress, study time, XP and streak fully localized.
* The assignment module relies on `whatsapp.assignment.*` keys to craft personalized help links.
* The **My course** area now includes certificate CTA plus accessible upcoming lessons.
MD,
        ],
        'admin-executive' => [
            'title' => 'Executive checklist',
            'content' => <<<'MD'
* Review the admin dashboard daily: metrics, teacher backlog, WhatsApp and certificates.
* The **Integrations Playbook** groups critical tokens and now links back to this Help Center.
* When issues arise, run `scripts/backend_role_smoke.php` or `scripts/real_integrations_smoke.php`.
MD,
        ],
    ],
];

