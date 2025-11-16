<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            ['group' => 'branding', 'name' => 'logo_horizontal_path', 'value' => ''],
            ['group' => 'branding', 'name' => 'logo_square_path', 'value' => ''],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['group' => $row['group'], 'name' => $row['name']],
                [
                    'payload' => json_encode($row['value']),
                    'locked' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'branding')
            ->whereIn('name', ['logo_horizontal_path', 'logo_square_path'])
            ->delete();
    }
};


