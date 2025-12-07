<?php

return [
    'contexts' => [
        'setup_integrations' => [
            'title' => 'Credentials checklist',
            'subtitle' => 'Review which services you need ready before finishing the wizard.',
            'cards' => [
                'video_streaming' => [
                    'title' => 'Video & streaming',
                    'summary' => 'Decide if you will use only YouTube or enable Vimeo/Cloudflare.',
                    'description' => 'For production we recommend enabling at least one protected provider (Vimeo or Cloudflare Stream).',
                    'tokens' => [
                        ['label' => 'YouTube', 'hint' => 'Define the domain in YOUTUBE_ORIGIN'],
                        ['label' => 'Vimeo', 'hint' => 'Token with scopes video_files + private'],
                        ['label' => 'Cloudflare', 'hint' => 'Account ID + Stream:Edit token'],
                    ],
                    'steps' => [
                        'Review the client privacy policy (is YouTube allowed?).',
                        'If you will not ship DRM day one, at least add the domain to the YouTube fallback.',
                        'When uploading long lessons, run a smoke test in /lessons/player.',
                    ],
                ],
                'automations' => [
                    'title' => 'Minimum automations',
                    'summary' => 'Google OAuth, Discord and Make enable planner micro-interactions.',
                    'description' => 'Without these credentials the planner and reminders will rely only on local emails.',
                    'tokens' => [
                        ['label' => 'Google OAuth', 'hint' => 'Verified Client ID / Secret'],
                        ['label' => 'Discord', 'hint' => 'Dedicated webhook for practices'],
                        ['label' => 'Make', 'hint' => 'Secure webhook with HMAC'],
                    ],
                    'steps' => [
                        'Enable Discord Developer mode and copy the thread for slot archives.',
                        'Generate a unique secret for Make and store it in the scenario.',
                        'Validate social login at /login → “Continue with Google”.',
                    ],
                ],
            ],
        ],
        'admin_dashboard' => [
            'title' => 'How to read this panel',
            'subtitle' => 'Operational checklist for Admin role.',
            'cards' => [
                'status' => [
                    'title' => 'Integration status',
                    'summary' => 'The bottom block shows if S3, Pusher, SMTP and telemetry respond.',
                    'description' => 'When you see “Pending” open Admin › Provisioner to refresh credentials.',
                    'tokens' => [
                        ['label' => 'DataPorter', 'hint' => 'active drivers and pending events'],
                        ['label' => 'S3 / R2', 'hint' => 'Synced bucket'],
                    ],
                    'steps' => [
                        'Click “View outbox” if pending/failed > 0.',
                        'Run `php artisan integration:status` in the console to confirm credentials.',
                        'Repeat after every deploy (smoke workflow).',
                    ],
                ],
                'telemetry' => [
                    'title' => 'Telemetry & QA',
                    'summary' => 'The viewed hours, drop-off and XP blocks depend on GA4/Mixpanel.',
                    'tokens' => [
                        ['label' => 'GA4 Enabled', 'hint' => 'Must be true to send player events'],
                        ['label' => 'Mixpanel', 'hint' => 'Optional for funnels'],
                    ],
                    'steps' => [
                        'Open Admin › DataPorter and review the sync panel.',
                        'If events are “pending”, run `php artisan telemetry:sync` or schedule the cron.',
                    ],
                ],
            ],
        ],
        'professor_dashboard' => [
            'title' => 'Shortcuts for Teacher Admin',
            'subtitle' => 'Plan practices and follow-ups from one place.',
            'cards' => [
                'planner' => [
                    'title' => 'Discord Planner',
                    'summary' => 'The “Discord Practices” widget uses Livewire planner data.',
                    'tokens' => [
                        ['label' => 'Templates', 'hint' => 'Configure cohorts in config/practice.php'],
                        ['label' => 'Discord threshold', 'hint' => 'Control when Admin is alerted'],
                    ],
                    'steps' => [
                        'Duplicate slots from the planner and confirm the counters update here.',
                        'When a student books, the “Reservations” counter increases.',
                        'If no data appears, ensure the `practice:sync` cron is active.',
                    ],
                ],
                'heatmap' => [
                    'title' => 'Heatmap & insights',
                    'summary' => '`video_heatmap_segments` feeds this widget. TelemetryRecorder must be active.',
                    'tokens' => [
                        ['label' => 'playerSignals', 'hint' => 'Must be loaded in resources/js/app.js'],
                    ],
                    'steps' => [
                        'Play the lesson with highest drop-off; the heatmap should match.',
                        'Export data from Admin › DataPorter for monthly reports.',
                    ],
                ],
            ],
        ],
        'student_dashboard' => [
            'title' => 'How to make the most of your panel',
            'subtitle' => 'Quick guide for Students.',
            'cards' => [
                'progress' => [
                    'title' => 'Progress bar & packs',
                    'summary' => 'The top widget combines XP, streak and practice reminders.',
                    'tokens' => [
                        ['label' => 'XP', 'hint' => 'Updates when you finish videos and assignments'],
                        ['label' => 'Pack reminder', 'hint' => 'Appears when a recommended slot is available'],
                    ],
                    'steps' => [
                        'Click “View practices” to jump directly to the filtered browser.',
                        'If you do not need the reminder, click “Dismiss” to clear the banner.',
                    ],
                ],
                'assignments' => [
                    'title' => 'Pending assignments',
                    'summary' => 'The bottom block summarizes tasks and feedback.',
                    'steps' => [
                        'Use the WhatsApp button if you need support; a contextual deep link is generated.',
                        'Each chip (Pending, Submitted, Approved) feeds from your real submissions.',
                    ],
                ],
            ],
        ],
    ],
    'routes' => [
        'lessons_player' => [
            'cards' => [
                'player' => [
                    'title' => 'Player UIX 2030',
                    'summary' => 'Explore the segmented bar and contextual CTAs.',
                    'steps' => [
                        'Markers indicate the end of each chapter; click to jump.',
                        'The contextual card switches between practices, packs and saved resources.',
                        'The “Resume from…” banner appears when returning to a half-finished lesson.',
                    ],
                ],
            ],
        ],
        'courses_builder' => [
            'cards' => [
                'builder' => [
                    'title' => 'Course Builder',
                    'summary' => 'Key shortcuts: N creates a chapter, Ctrl/Cmd+S saves the focused lesson.',
                    'steps' => [
                        'The focus panel has Content, Practice and Gamification tabs.',
                        'Use the practice/pack chips to open the planner in a new tab.',
                        'Duplicate or convert lessons from the quick menu (…).',
                    ],
                ],
            ],
        ],
        'admin_data_porter' => [
            'cards' => [
                'dataporter' => [
                    'title' => 'DataPorter Hub',
                    'summary' => 'Export filtered CSV/JSON and monitor GA4/Mixpanel sync.',
                    'steps' => [
                        'Select the dataset (video_player_events, student_activity_snapshots, etc.).',
                        'Apply course, category or date filters before exporting.',
                        'Use “Sync telemetry” to force manual sending.',
                    ],
                ],
            ],
        ],
        'student_discord_practices' => [
            'cards' => [
                'discord' => [
                    'title' => 'Discord bookings',
                    'summary' => 'Requires an active pack if the slot shows the lock.',
                    'steps' => [
                        'Filter by cohort or teacher from the sidebar.',
                        'Click “Book” to consume a session from the pack.',
                    ],
                ],
            ],
        ],
        'professor_discord_practices' => [
            'cards' => [
                'advanced_planner' => [
                    'title' => 'Advanced planner',
                    'summary' => 'Save templates with multiple slots and duplicate cohorts.',
                    'steps' => [
                        'Configure the template with Lesson, Channel, Capacity and requirements.',
                        'Use “Mass duplication” to create weekly series.',
                        'Apply a cohort Template to preload suggested schedules.',
                    ],
                ],
            ],
        ],
        'dashboard' => [
            'cards' => [
                'executive' => [
                    'title' => 'Executive summary',
                    'summary' => 'This dashboard changes according to your role.',
                    'steps' => [
                        'The top block shows general metrics and integration status.',
                        'Use the Playbook to validate credentials before every deploy.',
                        'Lower panels group WhatsApp, XP, certificates and outbox.',
                    ],
                ],
                'teacher_mode' => [
                    'title' => 'Teacher Admin Mode',
                    'summary' => 'Combines planner, practices and heatmaps.',
                    'steps' => [
                        'Check the critical integrations block (Discord, Make, WhatsApp).',
                        'Duplicate sessions from the “Discord Practices” widget and monitor bookings.',
                        'The heatmap highlights the lesson with most plays—use it to plan reinforcements.',
                    ],
                ],
                'student_panel' => [
                    'title' => 'Student panel',
                    'summary' => 'Gamification + reminders in one place.',
                    'steps' => [
                        'The four top counters summarize progress, time and XP.',
                        'When a pack is recommended, open the practices browser to book.',
                        'Task reminders include a WhatsApp deeplink for immediate support.',
                    ],
                ],
            ],
        ],
    ],
];


