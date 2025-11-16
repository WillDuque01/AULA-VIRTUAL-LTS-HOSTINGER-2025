<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ProfileCompletionReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class RemindIncompleteProfilesCommand extends Command
{
    protected $signature = 'profile:remind-incomplete {--threshold=80} {--dry-run}';

    protected $description = 'Envía recordatorios a usuarios con perfil incompleto.';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $cooldownDays = (int) config('profile.reminder_cooldown_days', 7);
        $dryRun = (bool) $this->option('dry-run');

        $query = User::query()
            ->where('profile_completion_score', '<', $threshold)
            ->where(function ($query) use ($cooldownDays) {
                $query->whereNull('profile_last_reminded_at')
                    ->orWhere('profile_last_reminded_at', '<=', now()->subDays($cooldownDays));
            });

        $count = 0;

        $query->chunkById(100, function ($users) use (&$count, $dryRun): void {
            foreach ($users as $user) {
                $summary = $user->profileSummary();

                if ($dryRun) {
                    $this->line(sprintf(
                        '[dry-run] %s (%s) → %d%%',
                        $user->email,
                        $user->name,
                        $summary['percent'] ?? 0
                    ));
                    $count++;
                    continue;
                }

                Notification::send($user, new ProfileCompletionReminderNotification($summary));
                $user->forceFill(['profile_last_reminded_at' => now()])->save();
                $count++;
            }
        });

        $this->info(sprintf('Recordatorios enviados: %d', $count));

        return self::SUCCESS;
    }
}

