<?php

namespace App\Support\Provisioning;

use App\Models\IntegrationAudit;
use App\Support\Integrations\IntegrationConfigurator;
use App\Support\Provisioning\Dto\ProvisioningMeta;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CredentialProvisioner
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function apply(array $payload, ?ProvisioningMeta $meta = null): void
    {
        $meta ??= ProvisioningMeta::fromDefaults();

        $booleanKeys = [
            'GOOGLE_SHEETS_ENABLED',
            'FORCE_FREE_STORAGE',
            'FORCE_FREE_REALTIME',
            'FORCE_YOUTUBE_ONLY',
            'AWS_USE_PATH_STYLE_ENDPOINT',
            'WHATSAPP_ENABLED',
        ];

        foreach ($booleanKeys as $boolKey) {
            $payload[$boolKey] = filter_var($payload[$boolKey] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        $auditableChanges = [];
        $debugSnapshot = [];

        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }

            $trimmed = is_string($value) ? trim($value) : $value;

            if ($trimmed === '') {
                continue;
            }

            $sanitized = Str::of($trimmed)
                ->replace(["\r", "\n"], '')
                ->limit(1024)
                ->toString();

            $previous = $this->currentEnvValue($key);
            $previousNormalized = is_bool($previous) ? ($previous ? 'true' : 'false') : (string) ($previous ?? '');
            $newNormalized = (string) $sanitized;

            $oldHash = $this->hashSecret($previousNormalized);
            $newHash = $this->hashSecret($newNormalized);

            $debugSnapshot[$key] = [
                'previous' => $previousNormalized,
                'new' => $newNormalized,
            ];

            $auditableChanges[$key] = [
                'old_hash' => $oldHash,
                'new_hash' => $newHash,
                'changed' => $oldHash !== $newHash,
            ];

            $this->writeEnv($key, $sanitized, $meta->shouldWriteEnv());
        }

        if ($meta->shouldCacheConfig()) {
            Artisan::call('config:clear');
            Artisan::call('config:cache');
        }

        IntegrationConfigurator::apply();

        if (! empty($auditableChanges)) {
            $this->rememberAudit($auditableChanges, $debugSnapshot);

            if ($meta->shouldPersistAudit() && $this->canPersistAudit()) {
                $this->persistAudit($auditableChanges, $meta);
            }
        }
    }

    private function writeEnv(string $key, string $value, bool $shouldWrite): void
    {
        if (! $shouldWrite) {
            return;
        }

        $path = base_path('.env');

        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path) ?: '';
        $safeValue = str_replace(['\\', '"'], ['\\\\', '\"'], $value);
        $line = sprintf('%s=%s', $key, $safeValue);
        $pattern = sprintf('/^%s=.*$/m', preg_quote($key, '/'));

        $content = preg_match($pattern, $content)
            ? preg_replace($pattern, $line, $content)
            : rtrim($content).PHP_EOL.$line;

        file_put_contents($path, $content);
    }

    private function hashSecret(?string $value): string
    {
        $normalized = $value ?? '';

        return hash('sha256', $normalized);
    }

    private function currentEnvValue(string $key): ?string
    {
        if (! app()->runningUnitTests()) {
            $path = base_path('.env');

            if (file_exists($path)) {
                foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (Str::startsWith($line, $key.'=')) {
                        $raw = substr($line, strlen($key) + 1);

                        return trim($raw, " \"'\r\n");
                    }
                }
            }
        }

        $value = Arr::get($_ENV, $key, env($key));

        if ($value === null) {
            return null;
        }

        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    private function rememberAudit(array $auditableChanges, array $debugSnapshot): void
    {
        app()->instance('provisioner.last_audit', $auditableChanges);

        foreach ($debugSnapshot as $key => $snapshot) {
            app()->instance("provisioner.debug.{$key}", $snapshot);
        }
    }

    private function canPersistAudit(): bool
    {
        try {
            return IntegrationAudit::query()
                ->getConnection()
                ->getSchemaBuilder()
                ->hasTable('integration_audits');
        } catch (Throwable $exception) {
            Log::warning('No se pudo verificar la existencia de la tabla integration_audits.', [
                'exception' => $exception,
            ]);

            return false;
        }
    }

    private function persistAudit(array $auditableChanges, ProvisioningMeta $meta): void
    {
        try {
            IntegrationAudit::create([
                'user_id' => optional($meta->user())->id,
                'changes' => $auditableChanges,
                'ip_address' => $meta->ipAddress(),
                'user_agent' => Str::of((string) $meta->userAgent())->limit(255)->toString(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('No se pudo registrar la auditoría de integraciones.', [
                'exception' => $exception,
            ]);
        }
    }
}
