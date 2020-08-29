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

declare(strict_types=1);

namespace Backup\Agent;

use Backup\Configuration;
use Backup\Exception\DatabaseException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Backup\Agent\Model\DatabaseModel;
use Backup\Agent\Model\DirectoryModel;
use Backup\Agent\Service\DatabaseService;
use Backup\Logger;
use Backup\Report\Report;
use Backup\Tool;
use PharException;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @package Backup\Agent
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Agent implements Backup
{
    use AnnotationInjection;

    private const TYPE_DIRECTORY = 'DIRECTORY';
    private const TYPE_DATABASE = 'DATABASE';

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
     * @var Report
     * @Inject("Backup\Report\Report")
     */
    private $report;

    /**
     * @var DatabaseService
     * @Inject("Backup\Agent\Service\DatabaseService")
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

            $status = Report::RESULT_OK;

            $message = '';

            if ($directoryModel->isDisabled()) {
                $status = Report::RESULT_INFO;

                $message = sprintf('Backup of directory "%s" is disabled.', $directoryModel->getName());

                $this->logger->use('app')->info($message);
            } else {
                try {
                    $this->backupDirectory($directoryModel);
                } catch (DirectoryException $e) {
                    $status = Report::RESULT_ERROR;

                    $message = $e->getMessage();

                    $this->logger->use('app')->error($message, [
                        'previous' => $e->getPrevious()->getMessage()
                    ]);
                }
            }

            $this->report->add($status, self::TYPE_DATABASE, $directoryModel, $message);
        }

        $databases = $this->config->getDatabases();

        if (!$databases) {
            $this->logger->use('app')->warning('No databases set in configuration.');
        }

        foreach ($databases as $database) {
            $databaseModel = new DatabaseModel($database);

            $status = Report::RESULT_OK;

            $message = '';

            if ($databaseModel->isDisabled()) {
                $status = Report::RESULT_INFO;

                $message = sprintf('Backup of database "%s" is disabled.', $databaseModel->getName());

                $this->logger->use('app')->info($message);
            } else {
                try {
                    $this->databaseService->backupDatabase($databaseModel);
                } catch (DatabaseException $e) {
                    $status = Report::RESULT_ERROR;

                    $message = $e->getMessage();

                    $this->logger->use('app')->error($message, [
                        'previous' => $e->getPrevious()->getMessage()
                    ]);
                }
            }

            $this->report->add($status, self::TYPE_DATABASE, $databaseModel, $message);
        }

        // Send report
        if ($this->config->isReportEnabled()) {
            if ($this->report->send()) {
                $this->logger->use('app')->debug('Report successfully sent.');
            } else {
                $this->logger->use('app')->error('Failed to sent report.');
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
