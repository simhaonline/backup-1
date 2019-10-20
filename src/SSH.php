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

namespace Backup;

/**
 * Class SSH
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class SSH
{

    /**
     * @var int
     */
    private $port = 22;

    /**
     * @var string
     */
    private $user = 'root';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $pass;

    /**
     * SSH constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->setPort($settings['port'] ?? $this->port);
        $this->setUser($settings['user'] ?? $this->user);
        $this->setKey($settings['key']);

        if (isset($settings['pass'])) {
            $this->setPass($settings['key']);
        }
    }

    /**
     * Set port
     *
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set key path
     *
     * @param string $path
     */
    public function setKey(string $path): void
    {
        $this->key = $path;
    }

    /**
     * Get key path
     *
     * @return string|null
     */
    public function getKey(): ? string
    {
        return $this->key;
    }

    /**
     * Set key path
     *
     * @param string $path
     */
    public function setPass(string $path): void
    {
        $this->pass = $path;
    }

    /**
     * Get key path
     *
     * @return string|null
     */
    public function getPass(): ? string
    {
        return $this->pass;
    }
}
