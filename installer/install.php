<?php

declare(strict_types=1);

/**
 * Instalador CLI para academias Hostinger.
 * Ejecutar: php install.php
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Este instalador sólo puede ejecutarse por CLI.\n");
    exit(1);
}

require __DIR__.'/src/helpers.php';
require __DIR__.'/src/Installer.php';

use Installer\Installer;

try {
    $installer = new Installer(__DIR__);
    $installer->run();
} catch (Throwable $e) {
    log_message($e->getMessage(), 'error');
    fwrite(STDERR, "❌ Error crítico: {$e->getMessage()}\n");
    exit(1);
}


