<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StorageMigrate extends Command
{
    protected $signature = 'storage:migrate
        {from=public : Disco origen, por defecto public}
        {to=s3 : Disco destino, por defecto s3}
        {--delete : Borra los archivos del disco origen después de copiar}
        {--dry-run : Muestra las acciones sin copiar archivos}';

    protected $description = 'Copia archivos entre discos configurados (ej. public → s3/R2)';

    public function handle(): int
    {
        $fromDisk = $this->argument('from');
        $toDisk = $this->argument('to');
        $dryRun = (bool) $this->option('dry-run');
        $shouldDelete = (bool) $this->option('delete');

        try {
            $source = Storage::disk($fromDisk);
        } catch (Throwable $e) {
            $this->error("El disco origen [$fromDisk] no está disponible: {$e->getMessage()}");

            return self::FAILURE;
        }

        try {
            $target = Storage::disk($toDisk);
        } catch (Throwable $e) {
            $this->error("El disco destino [$toDisk] no está disponible: {$e->getMessage()}");

            return self::FAILURE;
        }

        $files = $source->allFiles();

        if (empty($files)) {
            $this->info('No se encontraron archivos que migrar.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Migrando %d archivo(s) de [%s] → [%s]%s',
            count($files),
            $fromDisk,
            $toDisk,
            $dryRun ? ' (dry-run)' : ''
        ));

        $bar = $this->output->createProgressBar(count($files));
        $errors = [];

        foreach ($files as $path) {
            if ($dryRun) {
                $this->line("• Copiar {$path}");
                $bar->advance();
                continue;
            }

            $stream = $source->readStream($path);

            if ($stream === false) {
                $contents = $source->get($path);
                $result = $target->put($path, $contents);
            } else {
                $result = $target->writeStream($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (! $result) {
                $errors[] = $path;
            } elseif ($shouldDelete) {
                $source->delete($path);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! empty($errors)) {
            $this->error('No se pudieron migrar los siguientes archivos:');
            foreach ($errors as $failure) {
                $this->line('- '.$failure);
            }

            return self::FAILURE;
        }

        $this->info('Migración completada correctamente.');

        return self::SUCCESS;
    }
}
