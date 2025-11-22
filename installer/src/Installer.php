<?php

declare(strict_types=1);

namespace Installer;

use RuntimeException;

class Installer
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function run(): void
    {
        $this->welcome();
        $paths = $this->askTargetDirectory();
        $paths['archive'] = $this->askArchivePath();
        $env = $this->collectEnvData();

        $this->deploy([
            'paths' => $paths,
            'env' => $env,
        ]);
    }

    public function deploy(array $config): void
    {
        if (! isset($config['paths'], $config['env'])) {
            throw new RuntimeException('Configuración inválida para el instalador.');
        }

        $paths = $config['paths'];
        $env = $config['env'];

        if (! isset($paths['target'], $paths['public_path'], $paths['archive'])) {
            throw new RuntimeException('Faltan rutas requeridas en la configuración.');
        }

        $this->prepareDirectories($paths);
        $this->extractArchive($paths['archive'], $paths);
        $this->publishPublicFolder($paths, $paths['public_path']);
        $this->writeEnv($paths, $env);
        $this->runComposer($paths);
        $this->runMigrations($paths);
        $this->finalMessage($paths);
    }

    private function welcome(): void
    {
        echo "===== Instalador Academia Hostinger =====\n";
        echo "Este script desplegará el LMS en su cuenta actual.\n\n";
    }

    private function askTargetDirectory(): array
    {
        $home = getenv('HOME') ?: '/home/'.getenv('USER');
        $default = "{$home}/academia";
        $path = prompt("Ruta absoluta para instalar el LMS (default: {$default})");
        if ($path === '') {
            $path = $default;
        }

        $publicHtml = $home.'/public_html';
        $public = prompt("Ruta de public_html (default: {$publicHtml})");
        if ($public === '') {
            $public = $publicHtml;
        }

        if (! is_dir($public)) {
            throw new RuntimeException("La carpeta public_html no existe: {$public}");
        }

        return ['target' => $path, 'public_path' => $public];
    }

    private function askArchivePath(): string
    {
        $default = "{$this->basePath}/../lms.zip";
        $archive = prompt("Ruta del paquete ZIP del LMS (default: {$default})");
        if ($archive === '') {
            $archive = $default;
        }
        if (! file_exists($archive)) {
            throw new RuntimeException("No se encontró el archivo {$archive}");
        }

        return $archive;
    }

    private function collectEnvData(): array
    {
        echo "\n== Configuración de entorno ==\n";
        $env = [];
        $env['app_name'] = prompt('APP_NAME', false) ?: 'Academia';
        $env['app_url'] = prompt('APP_URL (https://dominio)', false);
        $env['db_host'] = prompt('DB_HOST', false) ?: 'localhost';
        $env['db_port'] = prompt('DB_PORT', false) ?: '3306';
        $env['db_database'] = prompt('DB_DATABASE', false);
        $env['db_username'] = prompt('DB_USERNAME', false);
        $env['db_password'] = prompt('DB_PASSWORD', true);
        $env['mail_host'] = prompt('MAIL_HOST (opcional)', false);
        $env['mail_username'] = $env['mail_host'] ? prompt('MAIL_USERNAME', false) : '';
        $env['mail_password'] = $env['mail_host'] ? prompt('MAIL_PASSWORD', true) : '';

        return $env;
    }

    private function prepareDirectories(array $paths): void
    {
        ['target' => $target] = $paths;
        if (is_dir($target)) {
            if (! confirm("La carpeta {$target} ya existe. ¿Sobrescribir?", false)) {
                throw new RuntimeException('Instalación cancelada.');
            }
            exec("rm -rf {$target}");
        }
        mkdir($target, 0755, true);
    }

    private function extractArchive(string $archive, array $paths): void
    {
        ['target' => $target] = $paths;
        echo "\n1/5 Descomprimiendo paquete...\n";
        $zip = new \ZipArchive();
        if ($zip->open($archive) !== true) {
            throw new RuntimeException('No se pudo abrir el ZIP.');
        }
        $zip->extractTo($target);
        $zip->close();
        log_message("Descomprimido en {$target}");
    }

    private function publishPublicFolder(array $paths, string $public): void
    {
        ['target' => $target] = $paths;
        $publicSource = "{$target}/public";
        echo "2/5 Publicando assets en {$public}...\n";

        exec("rm -rf {$public}/*");
        exec("cp -R {$publicSource}/* {$public}/");

        $index = "{$public}/index.php";
        $bootstrap = "{$target}/bootstrap/app.php";
        $autoload = "{$target}/vendor/autoload.php";
        $contents = <<<PHP
<?php

require __DIR__.'/../{$this->stripHome($autoload)}';
\$app = require_once __DIR__.'/../{$this->stripHome($bootstrap)}';

PHP;
        file_put_contents($index, $contents);
        log_message("public/ copiada a {$public}");
    }

    private function writeEnv(array $paths, array $env): void
    {
        ['target' => $target] = $paths;
        echo "3/5 Escribiendo .env...\n";
        $envTemplate = file_get_contents("{$target}/.env.example");
        $replacements = [
            'APP_NAME=Laravel' => "APP_NAME=\"{$env['app_name']}\"",
            'APP_URL=http://localhost' => "APP_URL={$env['app_url']}",
            'DB_HOST=127.0.0.1' => "DB_HOST={$env['db_host']}",
            'DB_PORT=3306' => "DB_PORT={$env['db_port']}",
            'DB_DATABASE=laravel' => "DB_DATABASE={$env['db_database']}",
            'DB_USERNAME=root' => "DB_USERNAME={$env['db_username']}",
            'DB_PASSWORD=' => "DB_PASSWORD=\"{$env['db_password']}\"",
        ];

        foreach ($replacements as $search => $replace) {
            $envTemplate = str_replace($search, $replace, $envTemplate);
        }

        if ($env['mail_host']) {
            $envTemplate = str_replace('MAIL_HOST=mailhog', "MAIL_HOST={$env['mail_host']}", $envTemplate);
            $envTemplate = str_replace('MAIL_USERNAME=null', "MAIL_USERNAME={$env['mail_username']}", $envTemplate);
            $envTemplate = str_replace('MAIL_PASSWORD=null', "MAIL_PASSWORD=\"{$env['mail_password']}\"", $envTemplate);
        }

        file_put_contents("{$target}/.env", $envTemplate);
        log_message(".env generado");
    }

    private function runComposer(array $paths): void
    {
        ['target' => $target] = $paths;
        echo "4/5 Ejecutando composer install...\n";
        $result = shell_exec("cd {$target} && php composer.phar install --no-dev --prefer-dist 2>&1");
        log_message($result ?: 'composer install ejecutado');
    }

    private function runMigrations(array $paths): void
    {
        ['target' => $target] = $paths;
        echo "5/5 Migrando base de datos...\n";
        $commands = [
            'php artisan key:generate',
            'php artisan migrate --force',
            'php artisan db:seed --force',
            'php artisan config:cache',
            'php artisan route:cache',
        ];

        foreach ($commands as $command) {
            $output = shell_exec("cd {$target} && {$command} 2>&1");
            log_message("{$command}: ".($output ?: 'ok'));
        }
    }

    private function finalMessage(array $paths): void
    {
        ['target' => $target] = $paths;
        echo "\n✅ Instalación completada en {$target}\n";
        echo "Verifica el sitio en el navegador y crea el cron `php {$target}/artisan schedule:run` en hPanel.\n";
        echo "Los logs se encuentran en installer/logs/install.log.\n";
    }

    private function stripHome(string $path): string
    {
        $home = getenv('HOME') ?: '/home/'.getenv('USER');
        return ltrim(str_replace($home.'/', '', $path), '/');
    }
}


