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

/**
 * Backup AgentException
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

require_once __DIR__ . '/config/path.php';
require_once VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    (new Bootstrap())->init()->run();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";

    $previous = $e->getPrevious()->getMessage() ?? false;

    if ($previous) {
        echo 'Previous error: ' . $previous . "\n";
    }

    exit();
}
