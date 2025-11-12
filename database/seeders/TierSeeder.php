<?php

namespace Database\Seeders;

use App\Models\StudentGroup;
use App\Models\Tier;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'tagline' => 'Contenido abierto y lecciones introductorias',
                'description' => 'Acceso gratuito a modulos basicos, anuncios y comunidad abierta.',
                'priority' => 0,
                'access_type' => 'free',
                'is_default' => true,
                'is_active' => true,
                'currency' => 'USD',
                'features' => ['basic-lessons', 'community-access'],
                'metadata' => ['color' => '#22c55e'],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'tagline' => 'Suscripcion completa para cursos premium',
                'description' => 'Incluye itinerarios completos, recursos descargables y soporte prioritario.',
                'priority' => 10,
                'access_type' => 'paid',
                'is_default' => false,
                'is_active' => true,
                'price_monthly' => 29.00,
                'price_yearly' => 290.00,
                'currency' => 'USD',
                'features' => ['premium-courses', 'downloadables', 'email-support'],
                'metadata' => ['color' => '#6366f1'],
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'tagline' => 'Mentoria personalizada y acceso ilimitado',
                'description' => 'Coaching 1:1, eventos privados y materiales exclusivos.',
                'priority' => 20,
                'access_type' => 'vip',
                'is_default' => false,
                'is_active' => true,
                'price_monthly' => 97.00,
                'price_yearly' => 970.00,
                'currency' => 'USD',
                'features' => ['coaching', 'private-events', 'lifetime-library'],
                'metadata' => ['color' => '#f97316'],
            ],
        ];

        foreach ($tiers as $data) {
            Tier::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $tierMap = Tier::whereIn('slug', collect($tiers)->pluck('slug'))->get()->keyBy('slug');

        $groups = [
            [
                'name' => 'Free Learners',
                'slug' => 'free-learners',
                'tier_slug' => 'free',
                'description' => 'Grupo abierto para estudiantes en modo gratuito.',
            ],
            [
                'name' => 'Pro Cohort 1',
                'slug' => 'pro-cohort-1',
                'tier_slug' => 'pro',
                'description' => 'Primer grupo guiado para suscriptores Pro.',
            ],
            [
                'name' => 'VIP Mentoring Circle',
                'slug' => 'vip-mentoring-circle',
                'tier_slug' => 'vip',
                'description' => 'Mentorias privadas para estudiantes VIP.',
            ],
        ];

        foreach ($groups as $group) {
            $tier = $tierMap->get($group['tier_slug'] ?? null);

            StudentGroup::updateOrCreate(
                ['slug' => $group['slug']],
                [
                    'name' => $group['name'],
                    'tier_id' => $tier?->id,
                    'description' => $group['description'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
