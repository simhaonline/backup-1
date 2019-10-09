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

namespace Backup;

use Backup\Exceptions\Database as DatabaseException;
use Backup\Exceptions\Directory as DirectoryException;
use Backup\Interfaces\Compressible;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @author BloodhunterD
 *
 * @package Backup
 */
class Agent
{

    use AnnotationInjection;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private $config;

    /**
     *
     */
    public function run(): void
    {
        $directories = $this->config->getDirectories();

        foreach ($directories as $directory) {
            $this->backupDirectory(new Directory($directory));
        }

        $databases = $this->config->getDatabases();

        foreach ($databases as $database) {
            $this->backupDatabase(new Database($database));
        }
    }

    /**
     * Backup a directory
     *
     * @param Directory $directory
     */
    public function backupDirectory($directory): void
    {
        $backupName = $directory->getName();

        $targetDirectory = $this->config->getTargetDirectory() . $directory->getTarget();

        if (!$this->createDirectory($targetDirectory)) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $backupName);

            throw new DirectoryException($msg);
        }

        if (!$this->createArchive($directory)) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $backupName);

            throw new DirectoryException($msg);
        }
    }

    /**
     * Backup a database
     *
     * @param Database $database
     */
    public function backupDatabase(Database $database): void
    {
        $backupName = $database->getName();

        $targetDirectory = $this->config->getTargetDirectory() . $database->getTarget();

        if (!$this->createDirectory($targetDirectory)) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $backupName);

            throw new DatabaseException($msg);
        }

        if (!$this->createDump($database)) {
            $msg = sprintf('Failed to create dump of database backup "%s".', $backupName);

            throw new DatabaseException($msg);
        }

        if (!$this->createArchive($database)) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $backupName);

            throw new DatabaseException($msg);
        }
    }

    /**
     * Create a directory
     *
     * @param string $path
     *
     * @return bool
     */
    public function createDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            $cmd = sprintf('mkdir -p %s', $path);

            $r = $this->execute($cmd);

            return $r && !is_dir($path);
        }

        return true;
    }

    /**
     * Create a dump
     *
     * @param Database $database
     *
     * @return bool
     */
    public function createDump(Database $database): bool
    {
        $sqlFile = $database->getName() . '.sql';

        if ($database->getType() === 'docker') {
            $cmd = sprintf(
                'docker exec %s sh -c \'exec mysqldump -uroot -p"\$MYSQL_ROOT_PASSWORD" \$MYSQL_DATABASE\' > %s',
                $database->getDockerContainer(),
                $sqlFile
            );
        } else {
            $excludedDatabases = ['information_schema', 'mysql', 'performance_schema'];

            $excludeSql = sprintf(' NOT IN (\'%s\')', implode('\',\'', $excludedDatabases));

            $databaseNameSql = sprintf(
                'mysql --skip-column-names -e "SELECT GROUP_CONCAT(schema_name SEPARATOR \' \') FROM information_schema.schemata WHERE schema_name%s;"',
                $excludeSql
            );

            $cmd = sprintf(
                'mysqldump -h %s -u %s -p "%s" --databases `%s` > %s',
                $database->getHost(),
                $database->getUser(),
                $database->getPassword(),
                $databaseNameSql,
                $sqlFile
            );
        }

        return $this->execute($cmd);
    }

    /**
     * Create an archive
     *
     * @param Compressible $object
     *
     * @return bool
     */
    public function createArchive(Compressible $object): bool
    {
        $cmd = sprintf('tar -cjf %s %s', $object->getArchive(), $object->getSource());

        return $this->execute($cmd);
    }

    /**
     * Execute a command
     *
     * @param string $command
     *
     * @return bool
     */
    public function execute(string $command): bool
    {
        exec(escapeshellcmd($command), $output, $return);

        unset($output);

        # The command failed, if it returns a non-zero value
        return ! (bool) $return;
    }
}
