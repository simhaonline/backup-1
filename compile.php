<?php
/**
 * This file is part of the Backup Tool project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2019 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

$start = microtime(true);

define('PHAR_FILE',  __DIR__ . '/build/backup.phar');

// Cleanup build folder
if (is_file(PHAR_FILE)) {
    unlink(PHAR_FILE);
}

// Cleanup composer
exec('composer install --no-dev');

$phar = new Phar(PHAR_FILE);
$phar->buildFromDirectory(__DIR__, '/config|res|src|vendor|composer|index/');

$phar->setDefaultStub('index.php');

// Reset composer to dev mode
exec('composer install');

$duration = round(microtime(true ) - $start, 3);

$size = round(filesize(PHAR_FILE) / (1024 ** 2), 3);

exit(sprintf('Phar of %s MB compiled in %s seconds.', $size, $duration));
