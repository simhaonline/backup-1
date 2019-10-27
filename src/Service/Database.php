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

namespace Backup\Service;

use Backup\Model\Database as DatabaseModel;

/**
 * Class Database
 *
 * @package Backup\Service
 *
 * @author BloodhunterD
 */
class Database
{

    /**
     * @var DatabaseModel
     */
    private $database;

    /**
     * Get database dump command
     *
     * @param DatabaseModel $database
     * @return string
     */
    public function getDumpCmd(DatabaseModel $database): string
    {
        $this->database = $database;

        return $this->database->getType() === 'docker' ? $this->getDockerDumpCmd() : $this->getHostDumpCmd();

    }

    /**
     * Get database dump command for Docker environments
     *
     * @return string
     */
    private function getDockerDumpCmd(): string
    {
        return sprintf(
            'docker exec %s sh -c \'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE\' > %s',
            escapeshellarg($this->database->getDockerContainer()),
            escapeshellarg($this->database->getSource())
        );
    }

    /**
     * Get database dump command for Host environments
     *
     * @return string
     */
    private function getHostDumpCmd(): string
    {
        $excludedDatabases = ['information_schema', 'mysql', 'performance_schema'];

        $excludeSql = sprintf(' NOT IN (\'%s\')', implode('\',\'', $excludedDatabases));

        $databaseNameSql = sprintf(
            'mysql --skip-column-names -e "SELECT GROUP_CONCAT(schema_name SEPARATOR \' \') FROM information_schema.schemata WHERE schema_name%s;"',
            $excludeSql
        );

        # Use password parameter only if a password is set
        $passwordSql = $this->database->getPassword() ? sprintf(' -p"%s"', $this->database->getPassword()) : '';

        return sprintf(
            'mysqldump -h%s -u%s%s --databases `%s` > %s',
            escapeshellarg($this->database->getHost()),
            escapeshellarg($this->database->getUser()),
            $passwordSql,
            $databaseNameSql,
            escapeshellarg($this->database->getSource())
        );
    }
}
