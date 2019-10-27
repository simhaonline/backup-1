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

use Backup\Interfaces\Compressible;

/**
 * Class DatabaseException
 *
 * @package Backup\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Database implements Compressible
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $archive;

    /**
     * @var string
     */
    private $type = 'host';

    /**
     * @var string
     */
    private $dockerContainer;

    /**
     * @var string
     */
    private $host = 'localhost';

    /**
     * @var string
     */
    private $user = 'root';

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target = DIRECTORY_SEPARATOR;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * Database constructor
     *
     * @param array $database
     *
     */
    public function __construct(array $database)
    {
        $source = $database['source'];

        # Required
        $this->setName($database['name']);

        # Optional
        $this->setTarget($database['target'] ?? $this->target);
        $this->setType($source['type'] ?? $this->type);

        if (isset($database['disabled']) && $database['disabled']) {
            $this->disable();
        }

        # Special handling for host or docker databases
        if ($this->type === 'docker') {
            # Required
            $this->setDockerContainer($source['container']);
        } else {
            # Optional
            $this->setHost($source['host'] ?? $this->host);
            $this->setUser($source['user'] ?? $this->user);
            $this->setPassword($source['password'] ?? $this->password);
        }

        $this->setSource('');
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
     * Set source
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
     * Set target
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
     * Set archive
     *
     * @param string $name
     */
    public function setArchive(string $name): void
    {
        $this->archive = $name . '.sql.bz2';
    }

    /**
     * Get archive
     *
     * @return string
     */
    public function getArchive(): string
    {
        return $this->archive;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set docker container
     *
     * @param string $container
     */
    public function setDockerContainer(string $container): void
    {
        $this->dockerContainer = $container;
    }

    /**
     * Get docker container
     *
     * @return string
     */
    public function getDockerContainer(): string
    {
        return $this->dockerContainer;
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
     * Get host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
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
     * Set password
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Disable
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * Is disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
