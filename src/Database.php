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

use Backup\Interfaces\Compressible;

/**
 * Class DatabaseException
 *
 * @package Backup
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
    private $type;

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
    private $password;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * Database constructor
     *
     * @param object $database
     *
     */
    public function __construct(object $database)
    {
        $this->setName($database->name);
        $this->setSource('');
        $this->setType($database->source->type);

        if ($this->type === 'docker') {
            $this->setDockerContainer($database->source->container);
        } else {
            $this->setHost($database->source->host);
            $this->setUser($database->source->user);
            $this->setPassword($database->source->pass);
        }

        $this->setTarget($database->target);
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
     * @param string|null $path
     */
    public function setTarget(string $path = null): void
    {
        $this->target = $path;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return $this->target ?? DIRECTORY_SEPARATOR;
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
     * @param string|null $type
     */
    public function setType(string $type = null): void
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType(): ?string
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
     * @param string|null $host
     */
    public function setHost(string $host = null): void
    {
        if (isset($host)) {
            $this->host = $host;
        }
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
     * @param string|null $user
     */
    public function setUser(string $user = null): void
    {
        if (isset($user)) {
            $this->user = $user;
        }
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
     * @param string|null $password
     */
    public function setPassword(string $password = null): void
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Create dump command
     *
     * @return string
     */
    public function createDumpCmd(): string
    {
        if ($this->type === 'docker') {
            return sprintf(
                'docker exec %s sh -c \'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE\' > %s',
                escapeshellarg($this->dockerContainer),
                escapeshellarg($this->source)
            );
        }

        $excludedDatabases = ['information_schema', 'mysql', 'performance_schema'];

        $excludeSql = sprintf(' NOT IN (\'%s\')', implode('\',\'', $excludedDatabases));

        $databaseNameSql = sprintf(
            'mysql --skip-column-names -e "SELECT GROUP_CONCAT(schema_name SEPARATOR \' \') FROM information_schema.schemata WHERE schema_name%s;"',
            $excludeSql
        );

        return sprintf(
            'mysqldump -h%s -u%s -p"%s" --databases `%s` > %s',
            escapeshellarg($this->host),
            escapeshellarg($this->user),
            $this->password,
            $databaseNameSql,
            escapeshellarg($this->source)
        );
    }
}
