<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoQuizSeeder extends Seeder
{
    public function run(): void
    {
        $lesson = DB::table('lessons')
            ->whereJsonContains('config->quiz_ref', 'a1-colores')
            ->first();
        if (!$lesson) {
            return;
        }

        if (DB::table('quizzes')->where('lesson_id', $lesson->id)->exists()) {
            return;
        }

        $quizId = DB::table('quizzes')->insertGetId([
            'lesson_id' => $lesson->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $q1 = DB::table('questions')->insertGetId([
            'quiz_id' => $quizId,
            'type' => 'mcq',
            'prompt' => '¿Cuál es el color "rojo"?',
            'meta' => json_encode(['shuffle' => true]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('options')->insert([
            ['question_id' => $q1, 'text' => 'Red', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1, 'text' => 'Blue', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1, 'text' => 'Green', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('questions')->insert([
            [
                'quiz_id' => $quizId,
                'type' => 'vf',
                'prompt' => 'El número 3 se escribe "three" en inglés.',
                'meta' => json_encode(['answer' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
