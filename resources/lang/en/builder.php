<?php

return [
    'heading' => 'Course builder: :slug',
    'description' => 'Organize chapters, define lessons, and control progression according to your syllabus.',

    'actions' => [
        'add_chapter' => 'New chapter',
        'add_chapter_aria' => 'Create new chapter',
        'add_chapter_title' => 'Create new chapter (shortcut: N)',
        'remove' => 'Remove',
    ],

    'metrics' => [
        'chapters' => 'Chapters',
        'drag_hint' => 'Drag & drop available',
        'total_lessons' => 'Total lessons',
        'lessons_hint' => 'Includes videos, quizzes, and more',
        'locks' => 'Active locks',
        'locks_hint' => 'Control progress · ≈ :hours h estimated',
    ],

    'shortcuts' => [
        'title' => 'Shortcuts & tips',
        'toggle_hide' => 'Hide',
        'toggle_show' => 'View shortcuts',
        'tagline' => 'Designed for 2030 flows · accessible and responsive.',
        'tip_new_chapter_title' => 'New chapter',
        'tip_new_chapter_hint' => 'Press N anywhere inside the builder.',
        'tip_save_title' => 'Save focused lesson',
        'tip_save_hint' => 'Press Ctrl/⌘ + S on any open card.',
        'tip_accessible_title' => 'Accessible drag & drop',
        'tip_accessible_hint' => 'Use Tab to focus the handle and Enter/Space to grab or drop.',
    ],

    'drag' => [
        'chapter_label' => 'Drag chapter',
        'chapter_hint' => 'Drag or use Enter/Space to reorder this chapter',
        'lesson_label' => 'Drag lesson',
        'lesson_hint' => 'Drag or use Enter/Space to reorder this lesson',
    ],

    'filter' => [
        'title' => 'Filter by status',
        'subtitle' => 'Show only chapters or lessons that match the selected status.',
    ],

    'chapter' => [
        'title_label' => 'Chapter title',
        'title_placeholder' => 'Untitled chapter',
        'empty_state' => 'No chapters yet. Use “New chapter” to get started.',
    ],

    'lessons' => [
        'title_label' => 'Title',
        'title_placeholder' => 'Lesson title',
        'type_label' => 'Type',
        'lock_toggle' => 'Lock progress',
        'remove' => 'Remove',
        'empty' => 'There are no lessons in this chapter yet.',
        'add' => [
            'video' => '+ Video',
            'text' => '+ Text',
            'pdf' => '+ PDF',
            'quiz' => '+ Quiz',
        ],
    ],

    'focus' => [
        'panel_label' => 'Focus panel',
        'default_lesson' => 'Selected lesson',
        'chapter_fallback' => 'Chapter',
        'tabs' => [
            'content' => 'Content',
            'config' => 'Configuration',
            'practice' => 'Practice',
            'gamification' => 'Gamification',
        ],
        'actions' => [
            'lesson_active' => 'Lesson in focus',
            'lesson_focus' => 'Focus lesson',
            'chip_active' => 'In focus',
            'chip_focus' => 'Focus',
            'close' => 'Close',
            'move_to' => 'Move to',
            'select_chapter' => 'Select chapter',
            'convert_to' => 'Convert to',
            'select_type' => 'Select type',
            'select_chapter_option' => 'Select chapter',
            'select_type_option' => 'Select type',
        ],
        'chips' => [
            'blocks_progress' => 'Blocks progress',
            'minutes' => 'min estimated',
            'release_on' => 'Releases on',
            'lessons_in_chapter' => 'lessons in the chapter',
        ],
        'content' => [
            'details' => 'Content details',
            'type' => 'Type',
            'duration' => 'Declared duration',
            'seconds' => 'sec',
            'prerequisite' => 'Prerequisite',
            'yes' => 'Yes',
            'no' => 'No',
            'cta' => 'Configured CTA',
            'cta_none' => 'No active CTA',
        ],
        'config_cards' => [
            'locks' => 'Locks',
            'locked' => 'Locked',
            'scheduled' => 'Scheduled release',
            'metadata' => 'Metadata',
            'badge' => 'Badge',
            'na' => 'N/A',
            'cta_label' => 'CTA label',
            'cta_url' => 'CTA URL',
            'defined' => 'Defined',
            'pending' => 'Pending',
        ],
        'practice' => [
            'practice_label' => 'Discord practices',
            'pack_required' => 'Pack required',
            'none' => 'No scheduled practices',
            'pack_assigned' => 'Assigned pack',
            'sessions' => 'sessions',
            'no_pack' => 'No linked pack',
            'open_planner' => 'Open Discord planner',
            'manage_packs' => 'Manage packs',
            'active' => 'Active practices',
            'next' => 'Next',
            'requires_pack' => 'Requires pack',
            'empty_state' => 'There are no practices linked to this lesson.',
        ],
        'assignments' => [
            'status' => 'Linked assignments status',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ],
    ],

    'advanced' => [
        'title' => 'Advanced settings',
        'open' => 'Expand',
        'close' => 'Close',
    ],

    'notifications' => [
        'lesson_saved' => 'Lesson saved',
    ],
];

