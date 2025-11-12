<?php

namespace App\Console\Commands;

use App\Models\IntegrationAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CredentialsAudit extends Command
{
    protected $signature = 'credentials:audit {--limit=20 : Número máximo de auditorías a mostrar}';

    protected $description = 'Muestra el historial reciente de cambios en credenciales de integraciones';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        if ($limit <= 0) {
            $this->error('El límite debe ser mayor a cero.');

            return self::INVALID;
        }

        $audits = IntegrationAudit::query()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();

        if ($audits->isEmpty()) {
            $this->warn('No hay auditorías registradas.');

            return self::SUCCESS;
        }

        $rows = $audits->map(function (IntegrationAudit $audit): array {
            $changedKeys = collect($audit->changes ?? [])
                ->filter(static fn (array $change): bool => $change['changed'] ?? false)
                ->keys()
                ->map(static fn (string $key): string => Str::upper($key))
                ->values()
                ->all();

            return [
                'Fecha' => optional($audit->created_at)->format('Y-m-d H:i:s') ?? '-'
                    ,
                'Usuario' => optional($audit->user)->email ?? 'N/D',
                'IP' => $audit->ip_address ?? '-',
                'Claves' => empty($changedKeys) ? '-' : implode(', ', $changedKeys),
            ];
        });

        $this->table(
            ['Fecha', 'Usuario', 'IP', 'Claves modificadas'],
            $rows->toArray()
        );

        return self::SUCCESS;
    }
}
