<?php

namespace App\Livewire\Lessons;

use App\Models\Lesson;
use App\Models\Option;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuizRunner extends Component
{
    public Lesson $lesson;

    public ?\App\Models\Quiz $quiz = null;

    public array $questions = [];

    public array $answers = [];

    public array $results = [];

    public ?QuizAttempt $lastAttempt = null;

    public bool $submitted = false;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->quiz = $lesson->quiz()->with(['questions.options'])->first();

        if ($this->quiz) {
            $this->questions = $this->quiz->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'type' => $question->type,
                    'prompt' => $question->prompt,
                    'meta' => $question->meta ?? [],
                    'options' => $question->options->map(function (Option $option) {
                        return [
                            'id' => $option->id,
                            'text' => $option->text,
                            'is_correct' => $option->is_correct,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        $this->loadLastAttempt();
    }

    public function submit(): void
    {
        $this->authorizeStudent();

        if (! $this->quiz) {
            return;
        }

        $score = 0;
        $details = [];

        foreach ($this->questions as $question) {
            $questionId = $question['id'];
            $answerValue = $this->answers[$questionId] ?? null;
            $isCorrect = false;

            if ($question['type'] === 'mcq') {
                $isCorrect = $this->validateMultipleChoice($question, $answerValue);
            } elseif ($question['type'] === 'vf') {
                $isCorrect = $this->validateTrueFalse($question, $answerValue);
            }

            if ($isCorrect) {
                $score++;
            }

            $details[$questionId] = [
                'answer' => $answerValue,
                'correct' => $isCorrect,
            ];
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'max_score' => count($this->questions),
            'answers' => $details,
        ]);

        $this->submitted = true;
        $this->results = [
            'score' => $score,
            'max_score' => max(1, count($this->questions)),
            'percentage' => count($this->questions) > 0 ? round(($score / count($this->questions)) * 100) : 0,
            'attempt_id' => $attempt->id,
        ];

        $this->lastAttempt = $attempt;
    }

    public function render()
    {
        return view('livewire.lessons.quiz-runner', [
            'lesson' => $this->lesson,
            'quiz' => $this->quiz,
            'questions' => $this->questions,
            'lastAttempt' => $this->lastAttempt,
        ]);
    }

    private function loadLastAttempt(): void
    {
        if (! $this->quiz || ! Auth::check()) {
            return;
        }

        $this->lastAttempt = $this->quiz->attempts()
            ->where('user_id', Auth::id())
            ->latest()
            ->first();
    }

    private function authorizeStudent(): void
    {
        abort_unless(Auth::check(), 403);
    }

    private function validateMultipleChoice(array $question, $answer): bool
    {
        if (! $answer) {
            return false;
        }

        $option = collect($question['options'])->firstWhere('id', (int) $answer);

        return (bool) ($option['is_correct'] ?? false);
    }

    private function validateTrueFalse(array $question, $answer): bool
    {
        if ($answer === null) {
            return false;
        }

        $expected = (bool) data_get($question, 'meta.answer', true);

        return filter_var($answer, FILTER_VALIDATE_BOOLEAN) === $expected;
    }
}


