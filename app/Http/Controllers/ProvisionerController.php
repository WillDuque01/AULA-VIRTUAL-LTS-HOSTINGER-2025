<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvisionerRequest;
use App\Support\Integrations\IntegrationConfigurator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ProvisionerController extends Controller
{
    public function save(ProvisionerRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $booleanKeys = [
            'GOOGLE_SHEETS_ENABLED',
            'FORCE_FREE_STORAGE',
            'FORCE_FREE_REALTIME',
            'FORCE_YOUTUBE_ONLY',
            'AWS_USE_PATH_STYLE_ENDPOINT',
        ];

        foreach ($booleanKeys as $boolKey) {
            $payload[$boolKey] = filter_var($payload[$boolKey] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

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

            $this->writeEnv($key, (string) $sanitized);
        }

        Artisan::call('config:clear');
        Artisan::call('config:cache');
        IntegrationConfigurator::apply();

        return response()->json([
            'status' => 'success',
            'message' => __('Settings updated successfully.'),
        ]);
    }

    private function writeEnv(string $key, string $value): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path) ?: '';
        $safeValue = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
        $line = sprintf('%s=%s', $key, $safeValue);
        $pattern = sprintf('/^%s=.*$/m', preg_quote($key, '/'));

        $content = preg_match($pattern, $content)
            ? preg_replace($pattern, $line, $content)
            : rtrim($content).PHP_EOL.$line;

        file_put_contents($path, $content);
    }
}