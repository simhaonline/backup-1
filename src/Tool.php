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

namespace Backup;

use Backup\Exception\ConfigurationException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Compressible;
use Locale;
use Phar;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Tool
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Tool
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
     * Set the timezone
     *
     * @param string $timezone
     * @throws ConfigurationException
     */
    public function setTimezone(string $timezone): void
    {
        if (!date_default_timezone_set($timezone)) {
            $msg = 'The timezone "%s" is invalid.';

            throw new ConfigurationException(sprintf($msg, $timezone));
        }

        $this->logger->use('app')->debug('Timezone successfully set');
    }

    /**
     * Set the language
     *
     * @param string $language
     * @throws ConfigurationException
     */
    public function setLanguage(string $language): void
    {
        if (!Locale::setDefault($language)) {
            $msg = 'The language "%s" is not supported or not installed.';

            throw new ConfigurationException(sprintf($msg, $language));
        }

        $this->logger->use('app')->debug('Language successfully set');
    }

    /**
     * Mount a directory
     *
     * @param string $path
     */
    public function mountDirectory(string $path): void
    {
        Phar::mount($path, $path);

        $this->logger->use('app')->debug(sprintf('Directory "%s" successfully mounted', $path));
    }

    /**
     * Create a directory
     *
     * @param string $path
     *
     * @throws ToolException
     */
    public function createDirectory(string $path): void
    {
        $absolutePath = $this->config->getTargetDirectory() . $path;

        if (!is_dir($absolutePath)) {
            $cmd = sprintf('mkdir -p %s', escapeshellarg($absolutePath));

            $this->execute($cmd);
        }

        $this->logger->use('app')->debug(sprintf('Directory "%s" successfully created', $absolutePath));
    }

    /**
     * Create an archive
     *
     * @param Compressible $object
     *
     * @throws ToolException
     */
    public function createArchive(Compressible $object): void
    {
        $target = $object->getTarget() . DIRECTORY_SEPARATOR . $object->getArchive();

        $cmd = sprintf(
            'tar -cjf %s %s',
            escapeshellarg($this->config->getTargetDirectory() . $target),
            escapeshellarg($object->getSource())
        );

        $this->execute($cmd);

        $this->logger->use('app')->debug(sprintf('Archive "%s" successfully created', $target));
    }

    /**
     * Execute a command
     *
     * @param string $command
     *
     * @return string[]
     * @throws ToolException
     */
    public function execute(string $command): array
    {
        unset($output);

        $this->logger->use('app')->debug(sprintf('Execute command: %s', $command));

        exec($command, $output, $return);

        foreach ($output as $line) {
            $this->logger->use('shell')->debug($line);
        }

        $this->logger->use('app')->debug(sprintf('Return status: %d', $return));

        # If the return status is not zero, the command failed
        if ($return !== 0) {
            throw new ToolException(sprintf('Failed to execute command: %s', $command), $return);
        }

        return $output;
    }

    /**
     * Sanitize a string
     *
     * @param string $string
     *
     * @return string
     */
    public function sanitize(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '-', preg_replace('/\s/', '_', $string));
    }
}
