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

use Backup\Model\SSHModel;

/**
 * Interface Downloadable
 *
 * @package Backup\Interfaces
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
interface Downloadable
{

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the source directory
     *
     * @return string
     */
    public function getSource(): string;

    /**
     * Get the target directory
     *
     * @return string
     */
    public function getTarget(): string;

    /**
     * Get the host address
     *
     * @return string
     */
    public function getHost(): string;

    /**
     * Get the SSH settings
     *
     * @return SSHModel
     */
    public function getSSH(): SSHModel;
}
