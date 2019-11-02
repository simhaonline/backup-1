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

namespace Backup\Interfaces;

use Backup\Exception\BackupException;

/**
 * Interface Backup
 *
 * @package Backup\Interfaces
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
interface Backup
{

    /**
     * Run the backup
     *
     * @throws BackupException
     */
    public function run(): void;
}
