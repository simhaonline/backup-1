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

use Backup\Exceptions\BackupAgent as BackupAgentException;

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    new BackupAgent\Bootstrap();

    // Todo: Do backup

    echo 'Backup Agent is running.';
} catch (BackupAgentException $e) {
    echo $e->getMessage();

    exit();
}
