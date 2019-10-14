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

use Backup\Bootstrap;
use Backup\Exception\BackupException;

/**
 * Backup AgentException
 *
 * @author BloodhunterD
 */

error_reporting(E_ALL ^ E_NOTICE | E_DEPRECATED);

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    (new Bootstrap())->init($argv[1])->run();
} catch (BackupException $e) {
    echo $e->getMessage() . "\n";

    exit();
}
