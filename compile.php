<?php
/**
 * This file is part of the Backup Agent project.
 * Visit project at https://github.com/bloodhunterd/backup-agent
 *
 * © BloodhunterD <backup-agent@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

/**
 * Phar Compiler
 */

$start = microtime(true);

define('PHAR_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('PHAR_FILE', PHAR_ROOT . 'build' . DIRECTORY_SEPARATOR . 'backup-agent.phar');
define('PHAR_FILE_COMP', PHAR_ROOT . 'build' . DIRECTORY_SEPARATOR . 'backup-agent.phar.bz2');

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
$phar->buildFromDirectory(PHAR_ROOT . 'app');

// Define initial script
$phar->setDefaultStub('index.php');

// Compress Phar
$phar->compress(Phar::BZ2);

$duration = round(microtime(true ) - $start, 3);

echo sprintf('Phar successfully compiled and compressed in %s seconds.', $duration);
