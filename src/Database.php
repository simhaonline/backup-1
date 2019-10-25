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
    private $password;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target = '';

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

        $this->setName($database['name']);
        $this->setSource('');
        $this->setType($source['type'] ?? $this->type);

        if ($database['disabled']) {
            $this->disable();
        }

        if ($this->type === 'docker') {
            $this->setDockerContainer($source['container']);
        } else {
            $this->setHost($source['host'] ?? $this->host);
            $this->setUser($source['user'] ?? $this->user);

            if ($source['pass']) {
                $this->setPassword($source['pass']);
            }
        }

        $this->setTarget($database['target'] ?? $this->target);
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
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     * Set host
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
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
     * Set password
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
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
