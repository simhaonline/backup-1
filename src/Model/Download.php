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

namespace Backup\Model;

use Backup\Interfaces\Downloadable;

/**
 * Class Downloadable
 *
 * @package Backup\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
abstract class Download implements Downloadable
{

    /**
     * Create download command
     *
     * @return string
     */
    public function createDownloadCmd(): string
    {
        # RSYNC:
        # -r Recursive
        # -t Preserves modification times.
        # -v Increases verbosity. (debug mode only)
        # -e Uses an alternative remote shell program for communication between the local and remote copies. (SSH)
        # SSH:
        # -q Quiet mode. Causes most warning and diagnostic messages to be suppressed.
        # -p Port to connect to on the remote host.
        # -i Identity file. Selects a file from which the identity (private key) for authentication is read.
        return sprintf(
            'rsync -r -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p %d -i %s" %s@%s:%s %s',
            $this->getSSH()->getPort(),
            escapeshellarg($this->getSSH()->getKey()),
            $this->getSSH()->getUser(),
            $this->getHost(),
            escapeshellarg($this->getSource() . DIRECTORY_SEPARATOR),
            escapeshellarg($this->getTarget() . DIRECTORY_SEPARATOR)
        );
    }
}
