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

/**
 * Class Configuration
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Configuration
{

    /**
     * @var object
     */
    private $settings;

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
    }

    /**
     * Load the settings from configuration file
     *
     * @throws ConfigurationException
     */
    public function load(): void
    {
        $json = file_get_contents(ROOT_DIR . DIRECTORY_SEPARATOR . 'config.json');

        $config = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        if (json_last_error()) {
            $msg = 'The configuration is invalid. Please check %s.';

            throw new ConfigurationException(sprintf($msg, json_last_error_msg()));
        }

        $this->settings = $config;
    }

    /**
     * Get the timezone
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->settings->timezone;
    }

    /**
     * Get the language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->settings->language;
    }

    /**
     * @return string|null
     */
    public function getMode(): ?string
    {
        return $this->settings->mode;
    }

    /**
     * Get the directories
     *
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->settings->sources->directories ?? [];
    }

    /**
     * Get the databases
     *
     * @return array
     */
    public function getDatabases(): array
    {
        return $this->settings->sources->databases ?? [];
    }

    /**
     * Get the servers
     *
     * @return array
     */
    public function getServers(): array
    {
        return $this->settings->sources->servers ?? [];
    }

    /**
     * Get the target directory
     *
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->settings->target->directory;
    }
}
