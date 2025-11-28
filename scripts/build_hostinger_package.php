<?php

declare(strict_types=1);

$root = realpath(__DIR__.'/..');
if ($root === false) {
    throw new RuntimeException('No se pudo resolver la ruta raíz del proyecto.');
}

$distDir = $root.'/dist';
$stagingDir = $distDir.'/validate_build';
$installerSrc = $root.'/installer';
$payloadZip = $distDir.'/hostinger_payload.zip';
$installerZip = $distDir.'/hostinger_installer.zip';
$installerMirror = $distDir.'/installer_bundle';
$bundleZip = $distDir.'/hostinger_bundle.zip';

if (! is_dir($stagingDir)) {
    throw new RuntimeException("No existe {$stagingDir}. Primero prepara la carpeta validate_build.");
}

if (! is_dir($distDir)) {
    mkdir($distDir, 0755, true);
}

deletePath($payloadZip);
deletePath($installerZip);
deletePath($installerMirror);
deletePath($bundleZip);

zipDirectory($stagingDir, $payloadZip, ['installer/', '.env']);
zipDirectory($installerSrc, $installerZip);
mirrorDirectory($installerSrc, $installerMirror);
bundleHostingerPackage($bundleZip, $installerMirror, $payloadZip);

echo "✅ Paquete LMS generado: {$payloadZip}\n";
echo "✅ Instalador (zip): {$installerZip}\n";
echo "✅ Instalador (carpeta): {$installerMirror}\n";
echo "✅ Paquete combinado installer+payload: {$bundleZip}\n";

/**
 * Zip a directory while ignoring provided prefixes.
 *
 * @param string $source
 * @param string $zipPath
 * @param array<int, string> $excludePrefixes
 */
function zipDirectory(string $source, string $zipPath, array $excludePrefixes = []): void
{
    $source = rtrim($source, '/\\');
    $zip = new \ZipArchive();
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("No se pudo crear el ZIP {$zipPath}");
    }

    $directory = new \RecursiveDirectoryIterator(
        $source,
        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
    );

    $iterator = new \RecursiveIteratorIterator($directory);
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $relative = relativePath($source, $file->getPathname());
        foreach ($excludePrefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($relative, $prefix)) {
                continue 2;
            }
        }

        $zip->addFile($file->getPathname(), $relative);
    }

    $zip->close();
}

function mirrorDirectory(string $source, string $destination): void
{
    $source = rtrim($source, '/\\');
    $destination = rtrim($destination, '/\\');
    if (! is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $directory = new \RecursiveDirectoryIterator(
        $source,
        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
    );

    $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $relative = relativePath($source, $item->getPathname());
        $targetPath = $destination.'/'.$relative;
        if ($item->isDir()) {
            if (! is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            continue;
        }

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }
        copy($item->getPathname(), $targetPath);
    }
}

function bundleHostingerPackage(string $bundleZip, string $installerDir, string $payloadZip): void
{
    $zip = new \ZipArchive();
    if ($zip->open($bundleZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("No se pudo crear el paquete combinado {$bundleZip}");
    }

    if (! is_dir($installerDir)) {
        throw new RuntimeException("No existe la carpeta del instalador en {$installerDir}");
    }

    $directory = new \RecursiveDirectoryIterator(
        $installerDir,
        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
    );

    $iterator = new \RecursiveIteratorIterator($directory);
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $relative = 'installer/'.relativePath($installerDir, $file->getPathname());
        $zip->addFile($file->getPathname(), $relative);
    }

    if (! file_exists($payloadZip)) {
        throw new RuntimeException("No se encontró {$payloadZip} para incluirlo en el bundle.");
    }

    $zip->addFile($payloadZip, 'hostinger_payload.zip');
    $zip->close();
}

function deletePath(string $path): void
{
    if (! file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }

    $directory = new \RecursiveDirectoryIterator(
        $path,
        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
    );
    $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($path);
}

function relativePath(string $base, string $path): string
{
    $base = rtrim($base, '/\\').'/';
    $relative = substr($path, strlen($base));
    return str_replace('\\', '/', ltrim($relative, '/'));
}

