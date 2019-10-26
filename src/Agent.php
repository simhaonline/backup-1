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

use Backup\Exception\DatabaseException;
use Backup\Exception\DirectoryException;
use Backup\Interfaces\Backup;
use Backup\Interfaces\Compressible;
use Backup\Model\Database;
use Backup\Model\Directory;
use PharException;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Agent implements Backup
{

    use AnnotationInjection;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private $config;

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
     * @inheritDoc
     */
    public function run(): void
    {
        $directories = $this->config->getDirectories();

        if (!$directories) {
            $this->logger->use('app')->warning('No directories set in configuration.');
        }

        foreach ($directories as $directory) {
            $directoryModel = new Directory($directory);

            if ($directoryModel->isDisabled()) {
                $this->logger->use('app')->debug(
                    sprintf('Backup of directory "%s" is disabled.', $directoryModel->getName())
                );

                continue;
            }

            try {
                $this->backupDirectory($directoryModel);
            } catch (DirectoryException $e) {
                $this->logger->use('app')->error($e->getMessage());

                continue;
            }
        }

        $databases = $this->config->getDatabases();

        if (!$databases) {
            $this->logger->use('app')->warning('No databases set in configuration.');
        }

        foreach ($databases as $database) {
            $databaseModel = new Database($database);

            if ($databaseModel->isDisabled()) {
                $this->logger->use('app')->debug(
                    sprintf('Backup of database "%s" is disabled.', $databaseModel->getName())
                );

                continue;
            }

            try {
                $this->backupDatabase($databaseModel);
            } catch (DatabaseException $e) {
                $this->logger->use('app')->error($e->getMessage());

                continue;
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param Directory $directory
     *
     * @throws DirectoryException
     */
    public function backupDirectory(Directory $directory): void
    {
        $name = $directory->getName();

        if (!$this->tool->createDirectory($directory->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }

        try {
            $this->tool->mountDirectory($directory->getSource());
        } catch (PharException $e) {
            throw new DirectoryException($e->getMessage());
        }

        $directory->setArchive($this->sanitize($directory->getName()));

        if (!$this->createArchive($directory)) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }

        $this->logger->use('app')->info(sprintf('Archive of directory "%s" successfully created.', $name));
    }

    /**
     * Backup a database
     *
     * @param Database $database
     *
     * @throws DatabaseException
     */
    public function backupDatabase(Database $database): void
    {
        $name = $database->getName();

        if (!$this->tool->createDirectory($database->getTarget())) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg);
        }

        $database->setSource($this->sanitize($database->getName()) . '.sql');

        if (!$this->tool->execute($database->createDumpCmd())) {
            $msg = sprintf('Failed to create dump of database backup "%s".', $name);

            throw new DatabaseException($msg);
        }

        $database->setArchive($this->sanitize($database->getName()));

        if (!$this->createArchive($database)) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $name);

            throw new DatabaseException($msg);
        }
    }

    /**
     * Create an archive
     *
     * @param Compressible $object
     *
     * @return bool
     */
    private function createArchive(Compressible $object): bool
    {
        $target = $object->getTarget() . DIRECTORY_SEPARATOR . $object->getArchive();

        $cmd = sprintf(
            'tar -cjf %s %s',
            escapeshellarg($this->config->getTargetDirectory() . $target),
            escapeshellarg($object->getSource())
        );

        return $this->tool->execute($cmd);
    }

    /**
     * Sanitize a string
     *
     * @param string $string
     *
     * @return string
     */
    private function sanitize(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '-', preg_replace('/\s/', '_', $string));
    }
}
