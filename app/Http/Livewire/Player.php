<?php

namespace App\Http\Livewire;

use App\Models\Lesson;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Player extends Component
{
    public Lesson $lesson;
    public array $config = [];
    public string $provider = 'youtube';
    public int $resumeAt = 0;
    public ?int $duration = null;
    public bool $strictMode = false;
    public bool $isVideo = true;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->config = $lesson->config ?? [];
        $this->isVideo = $lesson->type === 'video';
        $this->provider = $this->isVideo ? $this->resolveProvider() : 'static';
        $this->duration = $this->isVideo ? $this->resolveDuration() : null;
        $this->resumeAt = $this->isVideo ? $this->resolveResumePoint() : 0;
        $this->strictMode = $this->isVideo && $this->provider !== 'youtube';
    }

    public function render()
    {
        return view('livewire.player', [
            'provider' => $this->provider,
            'resumeAt' => $this->resumeAt,
            'duration' => $this->duration,
            'videoId' => $this->resolveVideoId(),
            'resourceUrl' => $this->resolveResourceUrl(),
            'isVideo' => $this->isVideo,
        ]);
    }

    private function resolveProvider(): string
    {
        $provider = (string) data_get($this->config, 'source', 'youtube');

        if (config('integrations.force_youtube_only')) {
            return 'youtube';
        }

        if (! in_array($provider, ['youtube', 'vimeo', 'cloudflare'], true)) {
            $provider = 'youtube';
        }

        if ($provider !== 'youtube') {
            $status = config('integrations.status.video.driver', 'youtube');
            if ($status === 'youtube') {
                $provider = 'youtube';
            }
        }

        return $provider;
    }

    private function resolveDuration(): ?int
    {
        $length = data_get($this->config, 'length');

        return $length !== null ? (int) $length : null;
    }

    private function resolveResumePoint(): int
    {
        $userId = Auth::id();
        if (! $userId) {
            return 0;
        }

        $progress = VideoProgress::where('lesson_id', $this->lesson->id)
            ->where('user_id', $userId)
            ->first();

        return $progress?->last_second ?? 0;
    }

    private function resolveVideoId(): ?string
    {
        if ($this->provider === 'youtube' || $this->provider === 'vimeo' || $this->provider === 'cloudflare') {
            return data_get($this->config, 'video_id');
        }

        return null;
    }

    private function resolveResourceUrl(): ?string
    {
        return data_get($this->config, 'resource_url');
    }
}


