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
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Backup\Model\DatabaseModel;
use Backup\Model\DirectoryModel;
use Backup\Service\DatabaseService;
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
     * @var DatabaseService
     * @Inject("Backup\Service\DatabaseService")
     */
    private $databaseService;

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
            $directoryModel = new DirectoryModel($directory);

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
            }
        }

        $databases = $this->config->getDatabases();

        if (!$databases) {
            $this->logger->use('app')->warning('No databases set in configuration.');
        }

        foreach ($databases as $database) {
            $databaseModel = new DatabaseModel($database);

            if ($databaseModel->isDisabled()) {
                $this->logger->use('app')->debug(
                    sprintf('Backup of database "%s" is disabled.', $databaseModel->getName())
                );

                continue;
            }

            try {
                $this->databaseService->backupDatabase($databaseModel);
            } catch (DatabaseException $e) {
                $this->logger->use('app')->error($e->getMessage(), [
                    'previous' => $e->getPrevious()->getMessage()
                ]);
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param DirectoryModel $directory
     *
     * @throws DirectoryException
     */
    public function backupDirectory(DirectoryModel $directory): void
    {
        $name = $directory->getName();

        try {
            $this->tool->createDirectory($directory->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        try {
            $this->tool->mountDirectory($directory->getSource());
        } catch (PharException $e) {
            $msg = sprintf('Failed to mount source directory for directory backup "%s"', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        $directory->setArchive($this->tool->sanitize($name));

        try {
            $this->tool->createArchive($directory);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        $this->logger->use('app')->info(sprintf('Archive of directory "%s" successfully created.', $name));
    }
}
