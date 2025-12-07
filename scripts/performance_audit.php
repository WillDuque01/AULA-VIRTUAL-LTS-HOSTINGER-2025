<?php
// [AGENTE: OPUS 4.5] - Script de auditoría de rendimiento

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║       AUDITORÍA DE RENDIMIENTO - LTS Academy                     ║\n";
echo "║       " . date('Y-m-d H:i:s') . "                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// 1. Estadísticas de tablas
echo "═══════════════════════════════════════════════════════════════════\n";
echo "1. ESTADÍSTICAS DE BASE DE DATOS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$tables = DB::select("
    SELECT 
        table_name,
        table_rows,
        ROUND(data_length/1024/1024, 2) AS data_mb,
        ROUND(index_length/1024/1024, 2) AS index_mb
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
    ORDER BY data_length DESC
    LIMIT 15
");

echo "Tabla                          | Filas      | Data MB | Index MB\n";
echo "-------------------------------|------------|---------|----------\n";
foreach ($tables as $table) {
    printf("%-30s | %10s | %7s | %8s\n", 
        $table->table_name, 
        number_format($table->table_rows ?? 0),
        $table->data_mb ?? '0.00',
        $table->index_mb ?? '0.00'
    );
}

// 2. Índices faltantes potenciales
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "2. ANÁLISIS DE ÍNDICES EN TABLAS CRÍTICAS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$criticalTables = ['users', 'courses', 'lessons', 'progress', 'certificates', 'messages', 'discord_practices', 'telemetry_events', 'jobs', 'failed_jobs'];

foreach ($criticalTables as $tableName) {
    try {
        $indexes = DB::select("SHOW INDEX FROM {$tableName}");
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        echo "📊 {$tableName}: " . count($indexNames) . " índices → " . implode(', ', array_slice($indexNames, 0, 5));
        if (count($indexNames) > 5) echo " ...";
        echo "\n";
    } catch (\Exception $e) {
        echo "⚠️ {$tableName}: No existe\n";
    }
}

// 3. Consultas críticas con EXPLAIN
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "3. ANÁLISIS DE CONSULTAS CRÍTICAS (EXPLAIN)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$queries = [
    'Prácticas futuras' => "SELECT * FROM discord_practices WHERE start_at > NOW() ORDER BY start_at ASC LIMIT 50",
    'Usuarios con rol' => "SELECT u.* FROM users u INNER JOIN model_has_roles mr ON u.id = mr.model_id WHERE mr.role_id = 1 LIMIT 100",
    'Progreso por usuario' => "SELECT * FROM progress WHERE user_id = 1 ORDER BY updated_at DESC LIMIT 50",
    'Certificados recientes' => "SELECT * FROM certificates ORDER BY created_at DESC LIMIT 20",
];

foreach ($queries as $name => $sql) {
    echo "🔍 {$name}:\n";
    try {
        $explain = DB::select("EXPLAIN " . $sql);
        if (!empty($explain)) {
            $row = $explain[0];
            $type = $row->type ?? 'N/A';
            $rows = $row->rows ?? 'N/A';
            $extra = $row->Extra ?? '';
            
            $status = '✅';
            if ($type === 'ALL') $status = '❌ FULL SCAN';
            elseif (str_contains($extra, 'filesort')) $status = '⚠️ filesort';
            elseif (str_contains($extra, 'temporary')) $status = '⚠️ temp table';
            
            echo "   Type: {$type} | Rows: {$rows} | {$status}\n";
            if ($extra) echo "   Extra: {$extra}\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 4. Estado de colas
echo "═══════════════════════════════════════════════════════════════════\n";
echo "4. ESTADO DE COLAS Y JOBS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

try {
    $pendingJobs = DB::table('jobs')->count();
    $failedJobs = DB::table('failed_jobs')->count();
    echo "📬 Jobs pendientes: {$pendingJobs}\n";
    echo "❌ Jobs fallidos: {$failedJobs}\n";
    
    if ($pendingJobs > 0) {
        $oldestJob = DB::table('jobs')->orderBy('created_at')->first();
        if ($oldestJob) {
            echo "⏰ Job más antiguo: " . date('Y-m-d H:i:s', $oldestJob->created_at) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "⚠️ No se pudo verificar colas: " . $e->getMessage() . "\n";
}

// 5. Conteo de registros principales
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "5. CONTEO DE REGISTROS PRINCIPALES\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$counts = [
    'users' => 'Usuarios',
    'courses' => 'Cursos',
    'lessons' => 'Lecciones',
    'chapters' => 'Capítulos',
    'progress' => 'Registros de progreso',
    'certificates' => 'Certificados',
    'messages' => 'Mensajes',
    'discord_practices' => 'Prácticas Discord',
    'practice_packages' => 'Paquetes de práctica',
    'practice_package_orders' => 'Órdenes de paquetes',
];

foreach ($counts as $table => $label) {
    try {
        $count = DB::table($table)->count();
        echo "📊 {$label}: " . number_format($count) . "\n";
    } catch (\Exception $e) {
        echo "⚠️ {$label}: tabla no existe\n";
    }
}

// 6. Configuración de caché
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "6. CONFIGURACIÓN DE CACHÉ\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📦 Driver de caché: " . config('cache.default') . "\n";
echo "📦 Driver de sesión: " . config('session.driver') . "\n";
echo "📦 Driver de cola: " . config('queue.default') . "\n";

// 7. Recomendaciones
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "7. RECOMENDACIONES DE OPTIMIZACIÓN\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$recommendations = [];

// Verificar índice en discord_practices.start_at
try {
    $indexes = DB::select("SHOW INDEX FROM discord_practices WHERE Column_name = 'start_at'");
    if (empty($indexes)) {
        $recommendations[] = "🔴 CRÍTICO: Agregar índice a discord_practices.start_at";
    }
} catch (\Exception $e) {}

// Verificar índice en progress.user_id
try {
    $indexes = DB::select("SHOW INDEX FROM progress WHERE Column_name = 'user_id'");
    if (empty($indexes)) {
        $recommendations[] = "🔴 CRÍTICO: Agregar índice a progress.user_id";
    }
} catch (\Exception $e) {}

// Verificar caché
if (config('cache.default') === 'file') {
    $recommendations[] = "🟡 MEDIO: Cambiar caché de 'file' a 'redis' o 'database' para mejor rendimiento";
}

// Verificar sesiones
if (config('session.driver') === 'file') {
    $recommendations[] = "🟡 MEDIO: Cambiar sesiones de 'file' a 'database' o 'redis'";
}

// Verificar telemetry
try {
    $telemetryCount = DB::table('telemetry_events')->count();
    if ($telemetryCount > 100000) {
        $recommendations[] = "🟡 MEDIO: Considerar archivar telemetry_events antiguos ({$telemetryCount} registros)";
    }
} catch (\Exception $e) {}

if (empty($recommendations)) {
    echo "✅ No se encontraron problemas críticos de optimización.\n";
} else {
    foreach ($recommendations as $rec) {
        echo "{$rec}\n";
    }
}

// 8. Estimación de capacidad
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "8. ESTIMACIÓN DE CAPACIDAD (100 USUARIOS SIMULTÁNEOS)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📊 CONFIGURACIÓN ACTUAL:\n";
echo "   • PHP-FPM max_children: 20 workers\n";
echo "   • Nginx worker_connections: 1024\n";
echo "   • RAM disponible: ~5GB\n";
echo "   • CPU cores: 2\n\n";

echo "📈 ESTIMACIÓN CON 100 USUARIOS SIMULTÁNEOS:\n\n";

echo "   ESCENARIO A: Navegación Normal (páginas estáticas/dashboard)\n";
echo "   ─────────────────────────────────────────────────────────────\n";
echo "   • Requests/segundo esperados: ~50-100 req/s\n";
echo "   • Tiempo respuesta estimado: 100-300ms\n";
echo "   • Estado: ✅ MANEJABLE\n\n";

echo "   ESCENARIO B: Uso Intensivo (player, streaming, prácticas)\n";
echo "   ─────────────────────────────────────────────────────────────\n";
echo "   • Requests/segundo esperados: ~200-400 req/s\n";
echo "   • Con 20 PHP workers: ~20 req concurrentes máximo\n";
echo "   • Posible cuello de botella: ⚠️ PHP-FPM\n";
echo "   • Tiempo respuesta estimado: 300-800ms\n";
echo "   • Estado: ⚠️ PUEDE DEGRADARSE\n\n";

echo "   ESCENARIO C: Pico de Carga (todos en video + telemetría)\n";
echo "   ─────────────────────────────────────────────────────────────\n";
echo "   • Requests/segundo esperados: ~500+ req/s\n";
echo "   • Cuello de botella: ❌ PHP-FPM + DB\n";
echo "   • Tiempo respuesta estimado: 1-3s+\n";
echo "   • Estado: ❌ DEGRADACIÓN PROBABLE\n\n";

echo "💡 OPTIMIZACIONES RECOMENDADAS PARA 100+ USUARIOS:\n";
echo "   1. Aumentar pm.max_children a 40-50\n";
echo "   2. Implementar Redis para caché y sesiones\n";
echo "   3. Agregar índices faltantes en BD\n";
echo "   4. Configurar OPcache agresivamente\n";
echo "   5. CDN para assets estáticos\n";
echo "   6. Considerar escalado horizontal (load balancer)\n";

echo "\n══════════════════════════════════════════════════════════════════\n";
echo "FIN DEL REPORTE\n";
echo "══════════════════════════════════════════════════════════════════\n";

