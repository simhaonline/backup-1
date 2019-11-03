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

use Backup\Exception\ConfigurationException;
use Phar;
use PharException;
use TypeError;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;
use Vection\Component\Validator\Schema\Schema;
use Vection\Component\Validator\Schema\SchemaValidator;
use Vection\Contracts\Validator\Schema\PropertyExceptionInterface;
use Vection\Contracts\Validator\Schema\SchemaExceptionInterface;

/**
 * Class Configuration
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Configuration
{

    use AnnotationInjection;

    /**
     * @var mixed[]
     */
    private $settings;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private $logger;

    /**
     * Mount the configuration file
     *
     * @throws ConfigurationException
     */
    public function mount(): void
    {
        try {
            Phar::mount('config.json', $_SERVER['argv'][1]);
        } catch (PharException | TypeError $e) {
            $msg = 'The configuration file is missing. Please check %s.';

            throw new ConfigurationException(sprintf($msg, $e->getMessage()));
        }

        $this->logger->use('app')->debug('Configuration successfully mounted');
    }

    /**
     * Load the settings from configuration file
     *
     * @throws ConfigurationException
     */
    public function load(): void
    {
        $validator = new SchemaValidator(new Schema(RES_DIR . DIRECTORY_SEPARATOR . 'config.schema.json'));

        $json = file_get_contents(ROOT_DIR . DIRECTORY_SEPARATOR . 'config.json');
        if ($json === false) {
            throw new ConfigurationException('Failed to load the configuration.');
        }

        try {
            $validator->validateJsonString($json);
        } catch (PropertyExceptionInterface | SchemaExceptionInterface $e) {
            $msg = 'The configuration is invalid. %s';

            throw new ConfigurationException(sprintf($msg, $e->getMessage()));
        }

        $this->settings = json_decode($json, true);

        $this->logger->use('app')->debug('Configuration successfully loaded');
    }

    /**
     * Get the timezone
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->settings['timezone'];
    }

    /**
     * Get the language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->settings['language'];
    }

    /**
     * Get the mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->settings['mode'];
    }

    /**
     * Get the report sender
     *
     * @return string[]
     */
    public function getReportSender(): array
    {
        return $this->settings['report']['sender'];
    }

    /**
     * Get report subject
     *
     * @return string
     */
    public function getReportSubject(): string
    {
        return $this->settings['report']['subject'];
    }

    /**
     * Get the report recipients
     *
     * @return mixed[]
     */
    public function getReportRecipients(): array
    {
        return $this->settings['report']['recipients'];
    }

    /**
     * Get the sources
     *
     * @return mixed[]
     */
    public function getSources(): array
    {
        return $this->settings['sources'];
    }

    /**
     * Get the directories
     *
     * @return mixed[]
     */
    public function getDirectories(): array
    {
        return $this->getSources()['directories'] ?? [];
    }

    /**
     * Get the databases
     *
     * @return mixed[]
     */
    public function getDatabases(): array
    {
        return $this->getSources()['databases'] ?? [];
    }

    /**
     * Get the servers
     *
     * @return mixed[]
     */
    public function getServers(): array
    {
        return $this->getSources()['servers'] ?? [];
    }

    /**
     * Get the target directory
     *
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->settings['target']['directory'];
    }
}
