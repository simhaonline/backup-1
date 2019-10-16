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

use Backup\Exception\AgentException;
use Backup\Exception\DatabaseException;
use Backup\Exception\DirectoryException;
use Backup\Interfaces\Compressible;
use Phar;
use PharException;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @package Backup
 *
 * @author BloodhunterD
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
     * Run backup agent
     */
    public function run(): void
    {
        $directories = $this->config->getDirectories();

        foreach ($directories as $directory) {
            try {
                $this->backupDirectory(new Directory($directory));
            } catch (AgentException | DirectoryException $e) {
                echo $e->getMessage() . "\n";

                continue;
            }
        }

        $databases = $this->config->getDatabases();

        foreach ($databases as $database) {
            try {
                $this->backupDatabase(new Database($database));
            } catch (AgentException | DatabaseException $e) {
                echo $e->getMessage() . "\n";

                continue;
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param Directory $directory
     *
     * @throws AgentException | DirectoryException
     */
    public function backupDirectory(Directory $directory): void
    {
        $name = $directory->getName();

        if (!$this->createDirectory($directory->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }

        $this->mountDirectory($directory->getSource());

        $directory->setArchive($this->sanitize($directory->getName()));

        if (!$this->createArchive($directory)) {
            $msg = sprintf('Failed to create archive for directory backup "%s".', $name);

            throw new DirectoryException($msg);
        }
    }

    /**
     * Backup a database
     *
     * @param Database $database
     *
     * @throws AgentException | DatabaseException
     */
    public function backupDatabase(Database $database): void
    {
        $name = $database->getName();

        if (!$this->createDirectory($database->getTarget())) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg);
        }

        $database->setSource($this->sanitize($database->getName()) . '.sql');

        if (!$this->execute($database->createDumpCmd())) {
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
     * Mount a directory
     *
     * @param string $path
     *
     * @throws AgentException
     */
    public function mountDirectory(string $path): void
    {
        try {
            Phar::mount($path, $path);
        } catch (PharException $e) {
            $msg = 'Failed to mount the target directory "%s". Please check %s.';

            throw new AgentException(sprintf($msg, $path, $e->getMessage()));
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

        return $this->execute($cmd);
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
