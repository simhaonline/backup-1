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

use Backup\Exception\DownloadException;
use Backup\Exception\DirectoryException;
use Backup\Interfaces\Backup;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Manager
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Manager implements Backup
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
        $servers = $this->config->getServers();

        if (!$servers) {
            $this->logger->use('app')->warning('No servers set in configuration.');
        }

        foreach ($servers as $server) {
            $serverModel = new Server($server);

            if ($serverModel->isDisabled()) {
                $this->logger->use('app')->debug(
                    sprintf('Backup of server "%s" is disabled.', $serverModel->getName())
                );

                continue;
            }

            try {
                $this->backupServer($serverModel);
            } catch (DownloadException | DirectoryException $e) {
                $this->logger->use('app')->error($e->getMessage());

                continue;
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param Server $server
     *
     * @throws DownloadException | DirectoryException
     */
    public function backupServer(Server $server): void
    {
        $name = $server->getName();

        if (!$this->tool->createDirectory($server->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }

        $server->setTarget($this->config->getTargetDirectory() . $server->getTarget());

        if (!$this->tool->execute($server->createDownloadCmd()) || !is_file($server->getTarget())) {
            $msg = sprintf('Failed to download from server "%s".', $name);

            throw new DownloadException($msg);
        }

        $this->logger->use('app')->info(sprintf('Download from server "%s" successfully.', $name));
    }
}
