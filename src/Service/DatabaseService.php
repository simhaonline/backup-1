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

use Backup\Exception\DatabaseException;
use Backup\Exception\ToolException;
use Backup\Logger;
use Backup\Model\DatabaseModel;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class DatabaseService
 *
 * @package Backup\Service
 *
 * @author BloodhunterD
 */
class DatabaseService
{

    use AnnotationInjection;

    /**
     * Excluded schemas
     */
    private const EXCLUDED_SCHEMAS = ['information_schema', 'mysql', 'performance_schema'];

    /**
     * @var DatabaseModel
     */
    private $database;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private $logger;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    private $tool;

    /**
     * Backup a database
     *
     * @param DatabaseModel $database
     *
     * @throws DatabaseException
     */
    public function backupDatabase(DatabaseModel $database): void
    {
        $this->database = $database;

        try {
            $this->tool->createDirectory($this->database->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $this->database->getName());

            throw new DatabaseException($msg, 0, $e);
        }

        if ($this->database->getType() === 'docker') {
            $cmd = $this->getDockerMySqlCmd($this->getSchemataQuery());
        } else {
            $cmd = $this->getHostMySqlCmd($this->getSchemataQuery());
        }

        # Get all available database schemata
        try {
            $schemata = $this->tool->execute($cmd);
        } catch (ToolException $e) {
            throw new DatabaseException('Failed to get database schemata.', 0, $e);
        }

        $schemata = explode(' ', $schemata[1]);

        foreach ($schemata as $schema) {
            try {
                $this->backupSchema($schema);
            } catch (DatabaseException $e) {
                $this->logger->use('app')->error($e->getMessage(), [
                    'previous' => $e->getPrevious()->getMessage()
                ]);
            }
        }
    }

    /**
     * Prepare host MySQL command
     *
     * @param string $query
     * @return string
     */
    private function getHostMySqlCmd(string $query): string
    {
        $cmd = 'mysql%s%s%s --skip-column-names -e \'%s;\'';

        return sprintf($cmd, $this->prepareHost(), $this->prepareUser(), $this->preparePassword(), $query);
    }

    /**
     * Prepare Docker MySQL command
     *
     * @param string $query
     * @return string
     */
    private function getDockerMySqlCmd(string $query): string
    {
        $cmd = 'docker exec %s sh -c "mysql%s%s --skip-column-names -e \'%s;\'"';

        return sprintf(
            $cmd,
            $this->database->getDockerContainer(),
            $this->prepareUser(),
            $this->preparePassword(),
            $query
        );
    }

    /**
     * Prepare host
     *
     * @return string
     */
    private function prepareHost(): string
    {
        $host = $this->database->getHost();

        return $host ? sprintf(' -h%s', escapeshellarg($host)) : '';
    }

    /**
     * Prepare user
     *
     * @return string
     */
    private function prepareUser(): string
    {
        $user = $this->database->getUser();

        return $user ? sprintf(' -u%s', escapeshellarg($user)) : '';
    }

    /**
     * Prepare password
     *
     * @return string
     */
    private function preparePassword(): string
    {
        $password = $this->database->getPassword();

        return $password ? sprintf(' -p%s', escapeshellarg($password)) : '';
    }

    /**
     * Get schemata query
     *
     * @return string
     */
    private function getSchemataQuery(): string
    {
        $query = 'SELECT
                    GROUP_CONCAT(schema_name SEPARATOR \" \")
                  FROM
                    information_schema.schemata
                  WHERE
                    schema_name NOT IN ("%s")
                  ';

        $query = sprintf($query, implode('\",\"', self::EXCLUDED_SCHEMAS));

        return $query;
    }

    /**
     * Backup schema
     *
     * @param string $schema
     * @throws DatabaseException
     */
    private function backupSchema(string $schema): void
    {
        $name = $this->database->getName();

        $this->database->setSource($this->tool->sanitize($name) . '.' . $schema . '.sql');

        try {
            $this->tool->execute($this->getSchemaDumpCmd($schema));
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create dump for schema "%s" of database backup "%s".', $schema, $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $this->database->setArchive($this->tool->sanitize($name) . '.' . $schema);

        try {
            $this->tool->createArchive($this->database);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive for schema %s of directory backup "%s".', $schema, $name);

            throw new DatabaseException($msg, 0, $e);
        }
    }

    /**
     * Get MySQL dump command
     *
     * @param string $schema
     * @return string
     */
    private function getSchemaDumpCmd(string $schema): string
    {
        return $this->database->getType() === 'docker' ? $this->getDockerDumpCmd($schema) : $this->getHostDumpCmd($schema);
    }

    /**
     * Get docker dump command
     *
     * @param string $schema
     * @return string
     */
    private function getDockerDumpCmd(string $schema): string
    {
        return sprintf(
            'docker exec %s sh -c \'mysqldump -u%s%s %s\' > %s',
            escapeshellarg($this->database->getDockerContainer()),
            $this->prepareUser(),
            $this->preparePassword(),
            escapeshellarg($schema),
            escapeshellarg($this->database->getSource())
        );
    }

    /**
     * Get host dump command
     *
     * @param string $schema
     * @return string
     */
    private function getHostDumpCmd(string $schema): string
    {
        return sprintf(
            'mysqldump -h%s -u%s%s %s > %s',
            $this->prepareHost(),
            $this->prepareUser(),
            $this->preparePassword(),
            escapeshellarg($schema),
            escapeshellarg($this->database->getSource())
        );
    }
}
