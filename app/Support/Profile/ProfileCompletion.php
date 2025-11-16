<?php

namespace App\Support\Profile;

use App\Models\User;

class ProfileCompletion
{
    /**
     * @return array{
     *     percent:int,
     *     steps:array<int,array{key:string,label:string,description:string,fields:array<int,string>,completed:bool,missing:array<int,string>}>,
     *     missing_fields:array<int,string>,
     *     is_complete:bool
     * }
     */
    public static function summarize(User $user): array
    {
        $steps = [
            [
                'key' => 'basic',
                'label' => __('Datos básicos'),
                'description' => __('Nombres y apellidos tal como aparecerán en certificados.'),
                'fields' => ['first_name', 'last_name'],
            ],
            [
                'key' => 'contact',
                'label' => __('Contacto'),
                'description' => __('Teléfono o WhatsApp para coordinaciones rápidas.'),
                'fields' => ['phone'],
            ],
            [
                'key' => 'location',
                'label' => __('Ubicación'),
                'description' => __('País, región y ciudad para personalizar tu plan.'),
                'fields' => ['country', 'state', 'city'],
            ],
        ];

        if ($user->hasAnyRole(['teacher', 'teacher_admin'])) {
            $steps[] = [
                'key' => 'teacher',
                'label' => __('Perfil docente'),
                'description' => __('Experiencia, idiomas y especialidades mostradas a los alumnos.'),
                'fields' => ['headline', 'bio', 'teaching_since', 'specialties', 'languages'],
            ];
        }

        $totalFields = collect($steps)->sum(fn ($step) => count($step['fields']));
        $completedFields = 0;
        $missingFields = [];

        $steps = collect($steps)->map(function (array $step) use ($user, &$completedFields, &$missingFields) {
            $missing = collect($step['fields'])
                ->reject(function (string $field) use ($user) {
                    $value = $user->{$field};

                    if (is_array($value)) {
                        return ! empty($value);
                    }

                    return filled($value);
                })
                ->values()
                ->all();

            $completed = count($step['fields']) - count($missing);
            $completedFields += $completed;

            foreach ($missing as $field) {
                $missingFields[] = $field;
            }

            $step['completed'] = empty($missing);
            $step['missing'] = $missing;

            return $step;
        })->values()->all();

        $percent = $totalFields > 0
            ? (int) round(($completedFields / $totalFields) * 100)
            : 100;

        return [
            'percent' => $percent,
            'steps' => $steps,
            'missing_fields' => $missingFields,
            'is_complete' => $percent >= 100,
        ];
    }

    public static function updateUserMetrics(User $user): void
    {
        $summary = self::summarize($user);
        $user->profile_completion_score = $summary['percent'];

        if ($summary['is_complete'] && ! $user->profile_completed_at) {
            $user->profile_completed_at = now();
        }
    }

    public static function syncDisplayName(User $user): void
    {
        $first = trim((string) $user->first_name);
        $last = trim((string) $user->last_name);

        if ($first !== '' || $last !== '') {
            $user->name = trim(sprintf('%s %s', $first, $last));
        }
    }
}

