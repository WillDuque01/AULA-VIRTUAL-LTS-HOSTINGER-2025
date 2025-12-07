<?php

return [
    'heading' => 'Builder de curso: :slug',
    'description' => 'Organiza capítulos, define lecciones y bloquea el avance según el plan de estudios.',

    'actions' => [
        'add_chapter' => 'Nuevo capítulo',
        'add_chapter_aria' => 'Crear nuevo capítulo',
        'add_chapter_title' => 'Crear nuevo capítulo (atajo: N)',
        'remove' => 'Eliminar',
    ],

    'metrics' => [
        'chapters' => 'Capítulos',
        'drag_hint' => 'Drag & drop disponible',
        'total_lessons' => 'Total lecciones',
        'lessons_hint' => 'Incluye videos, quizzes y más',
        'locks' => 'Bloqueos activos',
        'locks_hint' => 'Controla el progreso · ≈ :hours h estimadas',
    ],

    'shortcuts' => [
        'title' => 'Atajos y consejos',
        'toggle_hide' => 'Ocultar',
        'toggle_show' => 'Ver atajos',
        'tagline' => 'Diseñado para flujos 2030 · accesible y responsivo.',
        'tip_new_chapter_title' => 'Nuevo capítulo',
        'tip_new_chapter_hint' => 'Presiona N en cualquier parte del builder.',
        'tip_save_title' => 'Guardar lección enfocada',
        'tip_save_hint' => 'Ctrl/⌘ + S sobre cualquier tarjeta abierta.',
        'tip_accessible_title' => 'Drag & drop accesible',
        'tip_accessible_hint' => 'Usa la tecla Tab para enfocar el asa y Enter/Espacio para agarrar o soltar.',
    ],

    'drag' => [
        'chapter_label' => 'Arrastrar capítulo',
        'chapter_hint' => 'Arrastra o usa Enter/Espacio para reordenar este capítulo',
        'lesson_label' => 'Arrastrar lección',
        'lesson_hint' => 'Arrastra o usa Enter/Espacio para reordenar esta lección',
    ],

    'filter' => [
        'title' => 'Filtro por estado',
        'subtitle' => 'Muestra solo capítulos o lecciones con el estado seleccionado.',
    ],

    'chapter' => [
        'title_label' => 'Título del capítulo',
        'title_placeholder' => 'Capítulo sin título',
        'empty_state' => 'No hay capítulos por ahora. Usa el botón “Nuevo capítulo” para comenzar.',
    ],

    'lessons' => [
        'title_label' => 'Título',
        'title_placeholder' => 'Título de la lección',
        'type_label' => 'Tipo',
        'lock_toggle' => 'Bloquear avance',
        'remove' => 'Quitar',
        'empty' => 'No hay lecciones en este capítulo todavía.',
        'add' => [
            'video' => '+ Video',
            'text' => '+ Texto',
            'pdf' => '+ PDF',
            'quiz' => '+ Quiz',
        ],
    ],

    'focus' => [
        'panel_label' => 'Panel de enfoque',
        'default_lesson' => 'Lección seleccionada',
        'chapter_fallback' => 'Capítulo',
        'tabs' => [
            'content' => 'Contenido',
            'config' => 'Configuración',
            'practice' => 'Práctica',
            'gamification' => 'Gamificación',
        ],
        'actions' => [
            'lesson_active' => 'Lección en foco',
            'lesson_focus' => 'Enfocar lección',
            'chip_active' => 'En foco',
            'chip_focus' => 'Enfocar',
            'close' => 'Cerrar',
            'move_to' => 'Mover a',
            'select_chapter' => 'Selecciona capítulo',
            'convert_to' => 'Convertir a',
            'select_type' => 'Selecciona tipo',
            'select_chapter_option' => 'Selecciona capítulo',
            'select_type_option' => 'Selecciona tipo',
        ],
        'chips' => [
            'blocks_progress' => 'Bloquea avance',
            'minutes' => 'min estimados',
            'release_on' => 'Libera el',
            'lessons_in_chapter' => 'lecciones en el capítulo',
        ],
        'content' => [
            'details' => 'Detalles de contenido',
            'type' => 'Tipo',
            'duration' => 'Duración declarada',
            'seconds' => 'seg',
            'prerequisite' => 'Prerequisito',
            'yes' => 'Sí',
            'no' => 'No',
            'cta' => 'CTA configurado',
            'cta_none' => 'Sin CTA activo',
        ],
        'config_cards' => [
            'locks' => 'Bloqueos',
            'locked' => 'Bloqueada',
            'scheduled' => 'Liberación programada',
            'metadata' => 'Metadatos',
            'badge' => 'Badge',
            'na' => 'N/A',
            'cta_label' => 'CTA label',
            'cta_url' => 'CTA URL',
            'defined' => 'Definido',
            'pending' => 'Pendiente',
        ],
        'practice' => [
            'practice_label' => 'Prácticas Discord',
            'pack_required' => 'Pack requerido',
            'none' => 'Sin prácticas programadas',
            'pack_assigned' => 'Pack asignado',
            'sessions' => 'sesiones',
            'no_pack' => 'Sin pack vinculado',
            'open_planner' => 'Abrir planner Discord',
            'manage_packs' => 'Gestionar packs',
            'active' => 'Prácticas activas',
            'next' => 'Próxima',
            'requires_pack' => 'Requiere pack',
            'empty_state' => 'No hay prácticas vinculadas a esta lección.',
        ],
        'assignments' => [
            'status' => 'Estado de tareas vinculadas',
            'pending' => 'Pendientes',
            'approved' => 'Aprobadas',
            'rejected' => 'Rechazadas',
        ],
    ],

    'advanced' => [
        'title' => 'Configuración avanzada',
        'open' => 'Expandir',
        'close' => 'Cerrar',
    ],

    'notifications' => [
        'lesson_saved' => 'Lección guardada',
    ],
];
];

