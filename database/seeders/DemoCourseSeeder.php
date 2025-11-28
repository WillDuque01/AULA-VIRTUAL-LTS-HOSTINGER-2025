<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCourseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('courses')->where('slug', 'espanol-a1')->exists()) {
            return;
        }

        DB::transaction(function () {
            $courseId = DB::table('courses')->insertGetId([
                'slug' => 'espanol-a1',
                'level' => 'A1',
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('courses_i18n')->insert([
                ['course_id' => $courseId, 'locale' => 'es', 'title' => 'Español A1', 'description' => 'Curso inicial para principiantes.'],
                ['course_id' => $courseId, 'locale' => 'en', 'title' => 'Spanish A1', 'description' => 'Beginner course for absolute starters.'],
            ]);

            $chapters = [
                ['title' => 'Saludos y presentaciones'],
                ['title' => 'Números y colores'],
            ];
            $chapterIds = [];
            foreach ($chapters as $i => $c) {
                $chapterIds[$i] = DB::table('chapters')->insertGetId([
                    'course_id' => $courseId,
                    'title' => $c['title'],
                    'position' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $lessons = [
                [ 'chapter' => 0, 'type' => 'video', 'position' => 1,
                  'config' => json_encode(['source' => 'youtube', 'video_id' => 'dQw4w9WgXcQ', 'length' => 600]) ],
                [ 'chapter' => 0, 'type' => 'text',  'position' => 2,
                  'config' => json_encode(['html' => '<h2>Saludos básicos</h2><p>Hola, ¿qué tal?</p>']) ],
                [ 'chapter' => 0, 'type' => 'pdf',   'position' => 3,
                  'config' => json_encode(['url' => 'https://cdn.ejemplo.com/a1/guia-saludos.pdf']) ],
                [ 'chapter' => 1, 'type' => 'video', 'position' => 1,
                  'config' => json_encode(['source' => 'vimeo', 'video_id' => '123456789', 'length' => 540]) ],
                [ 'chapter' => 1, 'type' => 'iframe','position' => 2,
                  'config' => json_encode(['src' => 'https://juegos.ejemplo.com/colores']) ],
                [ 'chapter' => 1, 'type' => 'quiz',  'position' => 3,
                  'config' => json_encode(['quiz_ref' => 'a1-colores']) ],
            ];

            foreach ($lessons as $L) {
                DB::table('lessons')->insert([
                    'chapter_id' => $chapterIds[$L['chapter']],
                    'type' => $L['type'],
                    'config' => $L['config'],
                    'position' => $L['position'],
                    'locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
