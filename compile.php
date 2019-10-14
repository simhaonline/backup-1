<?php
/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2019 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

/**
 * Phar Compiler
 *
 * @author BlöoodhunterD
 */

$start = microtime(true);

$appFile = sprintf('backup.phar');

define('PHAR_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('PHAR_FILE', PHAR_ROOT . 'build' . DIRECTORY_SEPARATOR . $appFile);
define('PHAR_FILE_COMP', PHAR_ROOT . 'build' . DIRECTORY_SEPARATOR . $appFile . '.bz2');

// Remove existing Phar file
if (is_file(PHAR_FILE)) {
    unlink(PHAR_FILE);
}

// Remove existing compressed Phar file
if (is_file(PHAR_FILE_COMP)) {
    unlink(PHAR_FILE_COMP);
}

// Create new Phar file
$phar = new Phar(PHAR_FILE);

// Add sourcecode
$phar->buildFromDirectory(PHAR_ROOT, '[src|vendor|composer|index]');

// Define initial script
$phar->setDefaultStub('index.php');

// Compress Phar
$phar->compress(Phar::BZ2);

$duration = round(microtime(true ) - $start, 3);

exit(sprintf('Phar successfully compiled and compressed in %s seconds.', $duration));
