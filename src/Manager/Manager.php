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

namespace Backup\Manager;

use Backup\Configuration;
use Backup\Exception\DownloadException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Backup\Logger;
use Backup\Manager\Model\ServerModel;
use Backup\Manager\Service\DownloadService;
use Backup\Report\Report;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Manager
 *
 * @package Backup\Manager
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Manager implements Backup
{

    use AnnotationInjection;

    private const TYPE_SERVER = 'SERVER';

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
     * @inheritDoc
     */
    public function run(): void
    {
        $servers = $this->config->getServers();

        if (!$servers) {
            $this->logger->use('app')->warning('No servers set in configuration.');
        }

        foreach ($servers as $server) {
            $serverModel = new ServerModel($server);

            $status = Report::RESULT_OK;

            $message = '';

            if ($serverModel->isDisabled()) {
                $status = Report::RESULT_INFO;

                $message = sprintf('Backup of server "%s" is disabled.', $serverModel->getName());

                $this->logger->use('app')->info($message);
            } else {
                try {
                    $this->backupServer($serverModel);
                } catch (DownloadException | DirectoryException $e) {
                    $status = Report::RESULT_ERROR;

                    $message = $e->getMessage();

                    $this->logger->use('app')->error($message, [
                        'previous' => $e->getPrevious()->getMessage()
                    ]);
                }
            }

            $this->report->add($status, self::TYPE_SERVER, $serverModel, $message);
        }

        // Send report
        if ($this->config->isReportEnabled()) {
            if ($this->report->send()) {
                $this->logger->use('app')->info('Report sent.');
            } else {
                $this->logger->use('app')->error('Failed to sent report.');
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param ServerModel $server
     *
     * @throws DownloadException | DirectoryException
     */
    public function backupServer(ServerModel $server): void
    {
        $name = $server->getName();

        try {
            $this->tool->createDirectory($server->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for directory "%s".', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        $server->setTarget($this->config->getTargetDirectory() . $server->getTarget());

        try {
            $this->tool->execute((new DownloadService())->getCmd($server));
        } catch (ToolException $e) {
            $msg = sprintf('Failed to download from server "%s".', $name);

            throw new DownloadException($msg, 0, $e);
        }

        $this->logger->use('app')->info(sprintf('Archive "%s" downloaded from server.', $name));
    }
}
