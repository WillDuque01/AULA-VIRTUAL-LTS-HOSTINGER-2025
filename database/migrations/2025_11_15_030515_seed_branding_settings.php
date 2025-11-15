<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            ['group' => 'branding', 'name' => 'primary_color', 'value' => '#0f172a'],
            ['group' => 'branding', 'name' => 'secondary_color', 'value' => '#1d4ed8'],
            ['group' => 'branding', 'name' => 'accent_color', 'value' => '#f97316'],
            ['group' => 'branding', 'name' => 'font_family', 'value' => 'Inter, "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif'],
            ['group' => 'branding', 'name' => 'border_radius', 'value' => '0.75rem'],
            ['group' => 'branding', 'name' => 'dark_mode', 'value' => false],
            ['group' => 'branding', 'name' => 'logo_url', 'value' => url('/images/logo.png')],
            ['group' => 'branding', 'name' => 'logo_text', 'value' => config('app.name', 'Aula Virtual LTS')],
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
        DB::table('settings')->where('group', 'branding')->delete();
    }
};

