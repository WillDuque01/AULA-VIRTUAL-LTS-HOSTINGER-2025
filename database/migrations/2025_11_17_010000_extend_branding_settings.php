<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            ['group' => 'branding', 'name' => 'neutral_color', 'value' => '#cbd5f5'],
            ['group' => 'branding', 'name' => 'body_font_family', 'value' => 'Inter, "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif'],
            ['group' => 'branding', 'name' => 'type_scale_ratio', 'value' => '1.125'],
            ['group' => 'branding', 'name' => 'base_font_size', 'value' => '1rem'],
            ['group' => 'branding', 'name' => 'line_height', 'value' => '1.6'],
            ['group' => 'branding', 'name' => 'letter_spacing', 'value' => '0em'],
            ['group' => 'branding', 'name' => 'spacing_unit', 'value' => '0.5rem'],
            ['group' => 'branding', 'name' => 'shadow_soft', 'value' => '0 24px 48px rgba(15,23,42,0.16)'],
            ['group' => 'branding', 'name' => 'shadow_bold', 'value' => '0 35px 65px rgba(15,23,42,0.28)'],
            ['group' => 'branding', 'name' => 'container_max_width', 'value' => '1200px'],
            ['group' => 'branding', 'name' => 'logo_mode', 'value' => 'image'],
            ['group' => 'branding', 'name' => 'logo_svg', 'value' => '<svg viewBox="0 0 120 32" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Clash Display" font-size="22" fill="#fff">Aula</text></svg>'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['group' => $row['group'], 'name' => $row['name']],
                ['payload' => json_encode($row['value']), 'locked' => false, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'branding')
            ->whereIn('name', [
                'neutral_color',
                'body_font_family',
                'type_scale_ratio',
                'base_font_size',
                'line_height',
                'letter_spacing',
                'spacing_unit',
                'shadow_soft',
                'shadow_bold',
                'container_max_width',
                'logo_mode',
                'logo_svg',
            ])
            ->delete();
    }
};


