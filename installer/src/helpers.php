<?php

declare(strict_types=1);

namespace Installer;

const LOG_FILE = __DIR__.'/../logs/install.log';

function prompt(string $question, bool $hidden = false): string
{
    if ($hidden && stripos(PHP_OS, 'WIN') === false) {
        $command = "/usr/bin/env bash -c 'read -s -p \"{$question}: \" mypassword && echo \$mypassword'";
        $value = trim(shell_exec($command) ?? '');
        echo PHP_EOL;
    } else {
        echo $question.': ';
        $value = trim(fgets(STDIN) ?: '');
    }

    return $value;
}

function confirm(string $question, bool $defaultYes = true): bool
{
    $suffix = $defaultYes ? '[Y/n]' : '[y/N]';
    echo "{$question} {$suffix} ";
    $input = strtolower(trim(fgets(STDIN) ?: ''));
    if ($input === '') {
        return $defaultYes;
    }

    return in_array($input, ['y', 'yes', 's', 'si'], true);
}

function log_message(string $message, string $level = 'info'): void
{
    $date = date('Y-m-d H:i:s');
    $line = "[{$date}] {$level}: {$message}".PHP_EOL;
    if (! file_exists(dirname(LOG_FILE))) {
        mkdir(dirname(LOG_FILE), 0755, true);
    }
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
}


