<?php

return [
    'cohort_templates' => [
        'b1_morning' => [
            'name' => 'Cohorte B1 · Mañanas',
            'description' => 'Sesiones Lunes, Miércoles y Viernes · 09:00',
            'type' => 'cohort',
            'cohort_label' => 'B1-AM',
            'duration_minutes' => 60,
            'capacity' => 12,
            'requires_package' => true,
            'practice_package_id' => null,
            'slots' => [
                ['weekday' => 'monday', 'time' => '09:00'],
                ['weekday' => 'wednesday', 'time' => '09:00'],
                ['weekday' => 'friday', 'time' => '09:00'],
            ],
        ],
        'b2_evening' => [
            'name' => 'Cohorte B2 · Noches',
            'description' => 'Martes y Jueves · 19:30 + Repaso sábado 10:00',
            'type' => 'cohort',
            'cohort_label' => 'B2-PM',
            'duration_minutes' => 75,
            'capacity' => 10,
            'requires_package' => true,
            'practice_package_id' => null,
            'slots' => [
                ['weekday' => 'tuesday', 'time' => '19:30'],
                ['weekday' => 'thursday', 'time' => '19:30'],
                ['weekday' => 'saturday', 'time' => '10:00'],
            ],
        ],
        'coaching_global' => [
            'name' => 'Sesiones Coaching Global',
            'description' => 'Una sesión semanal abierta (miércoles 12:00)',
            'type' => 'global',
            'cohort_label' => 'Coaching',
            'duration_minutes' => 50,
            'capacity' => 20,
            'requires_package' => false,
            'practice_package_id' => null,
            'slots' => [
                ['weekday' => 'wednesday', 'time' => '12:00'],
            ],
        ],
    ],
];

