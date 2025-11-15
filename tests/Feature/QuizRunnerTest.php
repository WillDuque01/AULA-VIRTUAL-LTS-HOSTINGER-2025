<?php

namespace Tests\Feature;

use App\Livewire\Lessons\QuizRunner;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizRunnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_quiz_and_store_attempt(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createQuizLesson();
        $quiz = $lesson->quiz;
        $question = $quiz->questions->first();
        $correctOption = $question->options->firstWhere('is_correct', true);

        Livewire::actingAs($user)
            ->test(QuizRunner::class, ['lesson' => $lesson])
            ->set("answers.{$question->id}", $correctOption->id)
            ->call('submit')
            ->assertSet('results.score', 1)
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 1,
            'max_score' => 1,
        ]);
    }

    private function createQuizLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'quiz-course',
            'level' => 'a1',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Modulo 1',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'quiz',
            'position' => 1,
            'config' => [
                'title' => 'Quiz demo',
            ],
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'mcq',
            'prompt' => 'Selecciona la traducción correcta',
        ]);

        Option::create([
            'question_id' => $question->id,
            'text' => 'Red',
            'is_correct' => true,
        ]);

        Option::create([
            'question_id' => $question->id,
            'text' => 'Blue',
            'is_correct' => false,
        ]);

        $lesson->setRelation('quiz', $quiz->load('questions.options'));

        return $lesson;
    }
}


