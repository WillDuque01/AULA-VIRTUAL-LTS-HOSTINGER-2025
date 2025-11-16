<?php

namespace App\Console\Commands;

use App\Support\Telemetry\TelemetrySyncService;
use Illuminate\Console\Command;

class SyncTelemetryCommand extends Command
{
    protected $signature = 'telemetry:sync {--limit=200 : Número máximo de eventos a procesar}';

    protected $description = 'Sincroniza eventos del reproductor con los drivers externos configurados.';

    public function handle(TelemetrySyncService $service): int
    {
        $limit = (int) max(1, $this->option('limit'));

        $processed = $service->syncVideoEvents($limit);

        if ($processed === 0) {
            $this->info('No se encontraron eventos pendientes o no hay drivers habilitados.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Eventos sincronizados: %d', $processed));

        return self::SUCCESS;
    }
}

