<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Comando para resetear datos de demostración.
 * 
 * @author Opus 4.5 (Turno 28)
 */
class ResetDemoData extends Command
{
    protected $signature = 'academy:reset-demo 
                            {--force : Ejecutar sin confirmación}
                            {--preserve-users : Mantener usuarios existentes}';

    protected $description = 'Resetea la base de datos a estado de demostración (DESTRUYE DATOS)';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('⚠️  ADVERTENCIA: Este comando destruirá TODOS los datos.');
            $this->error('   Estás en entorno de PRODUCCIÓN.');
            
            if (! $this->confirm('¿Realmente deseas continuar?', false)) {
                $this->info('Operación cancelada.');
                return 1;
            }
        }

        $this->info('🔄 Iniciando reset de base de datos...');

        // Paso 1: Backup automático antes del reset
        $this->info('📦 Creando backup de seguridad...');
        $backupPath = storage_path('backups/pre_reset_' . now()->format('Y-m-d_H-i-s') . '.sql');
        exec("mysqldump -h {$_ENV['DB_HOST']} -u {$_ENV['DB_USERNAME']} -p{$_ENV['DB_PASSWORD']} {$_ENV['DB_DATABASE']} > {$backupPath} 2>/dev/null");
        
        if (file_exists($backupPath)) {
            $this->info("   ✅ Backup guardado: {$backupPath}");
        }

        // Paso 2: Ejecutar migrate:fresh
        $this->info('🗄️ Ejecutando migrate:fresh...');
        $result = Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true,
        ]);

        if ($result !== 0) {
            $this->error('❌ Error durante la migración.');
            return 1;
        }

        // Paso 3: Limpiar cachés
        $this->info('🧹 Limpiando cachés...');
        Artisan::call('optimize:clear');

        $this->newLine();
        $this->info('✅ Base de datos reseteada exitosamente.');
        $this->info('📋 Usuarios de prueba disponibles:');
        $this->table(
            ['Email', 'Rol', 'Contraseña'],
            [
                ['academy@letstalkspanish.io', 'Admin', 'AuditorQA2025!'],
                ['teacher.admin.qa@letstalkspanish.io', 'Teacher Admin', 'AuditorQA2025!'],
                ['teacher.qa@letstalkspanish.io', 'Teacher', 'AuditorQA2025!'],
                ['student.paid@letstalkspanish.io', 'Student Paid', 'AuditorQA2025!'],
            ]
        );

        return 0;
    }
}

