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
use Backup\Exception\ManagerException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ServerException;
use Backup\Interfaces\Downloadable;
use Phar;
use PharException;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Manager
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Manager
{

    use AnnotationInjection;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private $config;

    /**
     * Run backup agent
     */
    public function run(): void
    {
        $servers = $this->config->getServers();

        foreach ($servers as $server) {
            try {
                $this->backupServer(new Server($server));
            } catch (DownloadException | DirectoryException | ManagerException | ServerException $e) {
                echo $e->getMessage() . "\n";

                continue;
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param Server $server
     *
     * @throws DownloadException | DirectoryException | ManagerException | ServerException
     */
    public function backupServer(Server $server): void
    {
        $name = $server->getName();

        if (!$this->createDirectory($server->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }

        if (!$this->download($server)) {
            $msg = sprintf('Failed to download backup directory of server "%s".', $name);

            throw new DownloadException($msg);
        }
    }

    /**
     * Mount a directory
     *
     * @param string $path
     *
     * @throws ManagerException
     */
    public function mountDirectory(string $path): void
    {
        try {
            Phar::mount($path, $path);
        } catch (PharException $e) {
            $msg = 'Failed to mount the target directory "%s". Please check %s.';

            throw new ManagerException(sprintf($msg, $path, $e->getMessage()));
        }
    }

    /**
     * Create a directory
     *
     * @param string $path
     *
     * @return bool
     */
    private function createDirectory(string $path): bool
    {
        $absolutePath = $this->config->getTargetDirectory() . $path;

        if (!is_dir($absolutePath)) {
            $cmd = sprintf('mkdir -p %s', escapeshellarg($absolutePath));

            $r = $this->execute($cmd);

            return $r && is_dir($absolutePath);
        }

        return true;
    }

    /**
     * Execute a command
     *
     * @param string $command
     *
     * @return bool
     */
    private function execute(string $command): bool
    {
        exec($command, $output, $return);

        unset($output);

        # The command failed, if it returns a non-zero value
        return ! (bool) $return;
    }

    /**
     * Download all files from a downloadable object
     *
     * @param Downloadable $object
     *
     * @return bool
     */
    public function download(Downloadable $object): bool
    {
        # RSYNC:
        # -t Preserves modification times.
        # -v Increases verbosity. (debug mode only)
        # -e Uses an alternative remote shell program for communication between the local and remote copies. (SSH)
        # SSH:
        # -q Quiet mode. Causes most warning and diagnostic messages to be suppressed.
        # -p Port to connect to on the remote host.
        # -i Identity file. Selects a file from which the identity (private key) for authentication is read.
        $cmd = sprintf(
            'rsync -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p %d -i %s" %s@%s:%s %s/',
            $object->getSSH()->getPort(),
            $object->getSSH()->getKey(),
            $object->getSSH()->getUser(),
            $object->getHost(),
            $object->getSource(),
            $object->getTarget()
        );

        return $this->execute($cmd) && is_file($object->getTarget());
    }
}
