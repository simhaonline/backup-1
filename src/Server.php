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
 * Class Server
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Server extends Download
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target = '';

    /**
     * @var string
     */
    private $host;

    /**
     * @var SSH
     */
    private $ssh;

    /**
     * Server constructor
     *
     * @param array $server
     */
    public function __construct(array $server)
    {
        $this->setName($server['name']);
        $this->setSource($server['source']);
        $this->setTarget($server['target'] ?? $this->target);
        $this->setHost($server['host']);
        $this->setSSH(new SSH($server['ssh']));
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set source path
     *
     * @param string $path
     */
    public function setSource(string $path): void
    {
        $this->source = $path;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set target path
     *
     * @param string $path
     */
    public function setTarget(string $path): void
    {
        $this->target = $path;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set host
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set SSH
     *
     * @param SSH $ssh
     */
    public function setSSH(SSH $ssh): void
    {
        $this->ssh = $ssh;
    }

    /**
     * @inheritDoc
     */
    public function getSSH(): SSH
    {
        return $this->ssh;
    }
}
