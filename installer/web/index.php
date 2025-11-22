<?php

declare(strict_types=1);

use Installer\Installer;
use function Installer\log_message;

require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../src/Installer.php';

session_start();

$home = getenv('HOME') ?: '/home/'.getenv('USER');
$defaultArchive = realpath(__DIR__.'/../..').'/lms.zip';
$defaults = [
    'target_path' => $home.'/academia',
    'public_path' => $home.'/public_html',
    'archive_path' => file_exists($defaultArchive) ? $defaultArchive : '',
    'app_name' => 'Academia',
    'app_url' => '',
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_database' => '',
    'db_username' => '',
    'mail_host' => '',
    'mail_username' => '',
];

$errors = [];
$output = '';
$tokenRequired = getenv('INSTALLER_TOKEN') ?: '';

// Permitir que el token se defina en archivo (por ejemplo installer/token.txt)
$tokenFile = __DIR__.'/token.txt';
if ($tokenRequired === '' && file_exists($tokenFile)) {
    $tokenRequired = trim((string) file_get_contents($tokenFile));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $defaults;
    foreach ($defaults as $key => $value) {
        if (isset($_POST[$key])) {
            $data[$key] = trim((string) $_POST[$key]);
        }
    }
    $data['db_password'] = $_POST['db_password'] ?? '';
    $data['mail_password'] = $_POST['mail_password'] ?? '';

    if ($tokenRequired !== '' && ($tokenRequired !== ($_POST['access_token'] ?? ''))) {
        $errors[] = 'Token de acceso inválido.';
    }

    foreach (['target_path', 'public_path', 'archive_path', 'app_url', 'db_database', 'db_username'] as $key) {
        if ($data[$key] === '') {
            $errors[] = "El campo {$key} es obligatorio.";
        }
    }

    if (! is_dir(dirname($data['target_path']))) {
        $errors[] = 'La ruta destino no es válida o no existe.';
    }
    if (! is_dir($data['public_path'])) {
        $errors[] = 'La carpeta public_html no existe.';
    }
    if (! file_exists($data['archive_path'])) {
        $errors[] = 'No se encontró el archivo ZIP del proyecto.';
    }

    if (! $errors) {
        $config = [
            'paths' => [
                'target' => $data['target_path'],
                'public_path' => $data['public_path'],
                'archive' => $data['archive_path'],
            ],
            'env' => [
                'app_name' => $data['app_name'],
                'app_url' => $data['app_url'],
                'db_host' => $data['db_host'],
                'db_port' => $data['db_port'],
                'db_database' => $data['db_database'],
                'db_username' => $data['db_username'],
                'db_password' => $data['db_password'],
                'mail_host' => $data['mail_host'],
                'mail_username' => $data['mail_username'],
                'mail_password' => $data['mail_password'],
            ],
        ];

        try {
            $installer = new Installer(__DIR__.'/..');
            ob_start();
            $installer->deploy($config);
            $output = ob_get_clean();
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
            log_message($e->getMessage(), 'error');
        }
    }
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador Academia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7f7f7; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: .5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; }
        label { font-weight: 600; display:block; margin-bottom:.25rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap: 1rem; }
        .btn { background:#4f46e5; color:#fff; border:none; padding:.75rem 1.5rem; border-radius:4px; cursor:pointer; }
        .btn:disabled { opacity:.6; cursor:not-allowed; }
        .errors { background:#fee2e2; color:#991b1b; padding:1rem; border-radius:4px; margin-bottom:1rem; }
        pre { background:#000; color:#0f0; padding:1rem; border-radius:4px; max-height:360px; overflow:auto; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Instalador Academia (Hostinger)</h1>
        <p>Completa los campos y pulsa <strong>Desplegar</strong>. El instalador ejecutará los mismos pasos que el CLI.</p>

        <?php if ($errors): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php if ($tokenRequired !== ''): ?>
                <label>Token de acceso</label>
                <input type="password" name="access_token" required>
            <?php endif; ?>

            <h2>Rutas</h2>
            <label>Ruta destino (fuera de public_html)</label>
            <input type="text" name="target_path" value="<?= h($_POST['target_path'] ?? $defaults['target_path']) ?>" required>

            <label>Ruta public_html</label>
            <input type="text" name="public_path" value="<?= h($_POST['public_path'] ?? $defaults['public_path']) ?>" required>

            <label>Ruta ZIP del proyecto</label>
            <input type="text" name="archive_path" value="<?= h($_POST['archive_path'] ?? $defaults['archive_path']) ?>" required>

            <h2>Aplicación</h2>
            <div class="grid">
                <div>
                    <label>APP_NAME</label>
                    <input type="text" name="app_name" value="<?= h($_POST['app_name'] ?? $defaults['app_name']) ?>">
                </div>
                <div>
                    <label>APP_URL</label>
                    <input type="text" name="app_url" value="<?= h($_POST['app_url'] ?? $defaults['app_url']) ?>" placeholder="https://tu-dominio.com" required>
                </div>
            </div>

            <h2>Base de datos</h2>
            <div class="grid">
                <div>
                    <label>DB_HOST</label>
                    <input type="text" name="db_host" value="<?= h($_POST['db_host'] ?? $defaults['db_host']) ?>">
                </div>
                <div>
                    <label>DB_PORT</label>
                    <input type="text" name="db_port" value="<?= h($_POST['db_port'] ?? $defaults['db_port']) ?>">
                </div>
                <div>
                    <label>DB_DATABASE</label>
                    <input type="text" name="db_database" value="<?= h($_POST['db_database'] ?? $defaults['db_database']) ?>" required>
                </div>
                <div>
                    <label>DB_USERNAME</label>
                    <input type="text" name="db_username" value="<?= h($_POST['db_username'] ?? $defaults['db_username']) ?>" required>
                </div>
                <div>
                    <label>DB_PASSWORD</label>
                    <input type="password" name="db_password">
                </div>
            </div>

            <h2>Correo (opcional)</h2>
            <div class="grid">
                <div>
                    <label>MAIL_HOST</label>
                    <input type="text" name="mail_host" value="<?= h($_POST['mail_host'] ?? $defaults['mail_host']) ?>">
                </div>
                <div>
                    <label>MAIL_USERNAME</label>
                    <input type="text" name="mail_username" value="<?= h($_POST['mail_username'] ?? $defaults['mail_username']) ?>">
                </div>
                <div>
                    <label>MAIL_PASSWORD</label>
                    <input type="password" name="mail_password">
                </div>
            </div>

            <button class="btn" type="submit">Desplegar academia</button>
        </form>

        <?php if ($output): ?>
            <h2>Resultado</h2>
            <pre><?= h($output) ?></pre>
            <p>Recuerda borrar la carpeta <code>installer/</code> después de verificar el sitio.</p>
        <?php endif; ?>
    </div>
</body>
</html>

