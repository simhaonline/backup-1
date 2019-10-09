<?php
/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * © BloodhunterD <backup@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

use Backup\Exceptions\BackupAgent as BackupAgentException;

error_reporting(E_ALL ^ E_NOTICE | E_DEPRECATED);

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    (new Backup\Bootstrap())->init()->run();
} catch (BackupAgentException $e) {
    echo $e->getMessage();

    exit();
}
