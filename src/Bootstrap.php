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
use Backup\Exception\ConfigurationException;
use Backup\Exception\ManagerException;
use Locale;
use Vection\Component\DI\Container;

/**
 * Class Bootstrap
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Bootstrap
{

    /**
     * @var Container
     */
    public $container;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->registerNamespace([
            'Backup'
        ]);
    }

    /**
     * Initialize the backup application
     *
     * @return Agent | Manager
     * @throws AgentException | ConfigurationException | ManagerException
     */
    public function init(): object
    {
        $config = new Configuration();
        $config->mount();
        $config->load();

        $this->container->add($config);

        $this->setTimezone($config->getTimezone());
        $this->setLanguage($config->getLanguage());

        switch ($config->getMode()) {
            case 'agent':
                /** @var Agent $backup */
                $backup = $this->container->get(Agent::class);
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);
                break;
            default:
                throw new ConfigurationException(sprintf('The mode "%s" is invalid.', $config->getMode()));
        }

        $backup->mountDirectory($config->getTargetDirectory());

        return $backup;
    }

    /**
     * Set the timezone
     *
     * @param string $timezone
     * @throws ConfigurationException
     */
    private function setTimezone(string $timezone): void
    {
        if (!date_default_timezone_set($timezone)) {
            $msg = 'The timezone "%s" is invalid.';

            throw new ConfigurationException(sprintf($msg, $timezone));
        }
    }

    /**
     * Set the language
     *
     * @param string $language
     * @throws ConfigurationException
     */
    private function setLanguage(string $language): void
    {
        if (!Locale::setDefault($language)) {
            $msg = 'The language "%s" is not supported or not installed.';

            throw new ConfigurationException(sprintf($msg, $language));
        }
    }
}
