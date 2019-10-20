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
use Backup\Interfaces\Downloadable;
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
            $this->logger->use('app')->warning('No servers set in configuration');
        }

        foreach ($servers as $server) {
            try {
                $this->backupServer(new Server($server));
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

        if (!$this->download($server)) {
            $msg = sprintf('Failed to download from server "%s".', $name);

            throw new DownloadException($msg);
        }

        $this->logger->use('app')->info(sprintf('Download from server "%s" successfully', $name));
    }

    /**
     * Download all files from a downloadable object
     *
     * @param Downloadable $downloadable
     *
     * @return bool
     */
    public function download(Downloadable $downloadable): bool
    {
        # RSYNC:
        # -r Recursive
        # -t Preserves modification times.
        # -v Increases verbosity. (debug mode only)
        # -e Uses an alternative remote shell program for communication between the local and remote copies. (SSH)
        # SSH:
        # -q Quiet mode. Causes most warning and diagnostic messages to be suppressed.
        # -p Port to connect to on the remote host.
        # -i Identity file. Selects a file from which the identity (private key) for authentication is read.
        $cmd = sprintf(
            'rsync -r -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p %d -i %s" %s@%s:%s %s/',
            $downloadable->getSSH()->getPort(),
            $downloadable->getSSH()->getKey(),
            $downloadable->getSSH()->getUser(),
            $downloadable->getHost(),
            $downloadable->getSource(),
            $downloadable->getTarget()
        );

        return $this->tool->execute($cmd) && is_file($downloadable->getTarget());
    }
}
