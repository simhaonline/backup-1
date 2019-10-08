<?php

/**
 * This file is part of the Backup Agent project.
 * Visit project at https://github.com/bloodhunterd/backup-agent
 *
 * © BloodhunterD <backup-agent@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BackupAgent;

use Backup\Exceptions\Configuration as ConfigurationException;
use Phar;
use PharException;

/**
 * Class Configuration
 *
 * @package BackupAgent
 */
class Configuration
{

    /**
     * @var object
     */
    private $settings;

    /**
     * Mount the config file
     *
     * @throws ConfigurationException
     */
    public function mount(): void
    {
        try {
            Phar::mount('config.json', $_SERVER['argv'][1]);
        } catch (PharException $exception) {
            $msg = 'Failed to load the config file "%s". Check the file path or try to place it into the same directory.';

            throw new ConfigurationException(sprintf($msg, $_SERVER['argv'][1]));
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

        $config = json_decode($json);

        if (json_last_error()) {
            $msg = sprintf('Configuration is broken. Please check %s.', json_last_error_msg());

            throw new ConfigurationException(sprintf($msg, $_SERVER['argv'][1]));
        }

        $this->settings = $config;
    }

    /**
     * Get the configuration settings
     *
     * @return object
     */
    public function getSettings(): object
    {
        return $this->settings;
    }
}
