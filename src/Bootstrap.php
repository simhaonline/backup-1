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
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
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
    private $container;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        # Initialize dependency injection
        $this->container = new Container();
        $this->container->registerNamespace([
            'Backup'
        ]);
    }

    /**
     * Initialize the backup application
     *
     * @return Agent | Manager
     * @throws ConfigurationException | Exception
     */
    public function init(): object
    {
        # Wrap loggers to be able to inject
        $loggers = new Logger();
        # Initialize application logging
        $loggers->set((new MonologLogger('app'))
            ->pushHandler(new StreamHandler('php://stdout'))
            ->pushHandler(new StreamHandler('/var/log/backup.log'))
        );
        # Initialize report logging
        $loggers->set((new MonologLogger('report'))
            ->pushHandler(
                new StreamHandler(ROOT_DIR . DIRECTORY_SEPARATOR . 'backup-report.log', MonologLogger::INFO)
            )
        );

        # Make logger injectable
        $this->container->add($loggers);

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);
        $config->mount();
        $config->load();

        $tool = $this->container->get(Tool::class);

        $tool->setTimezone($config->getTimezone());
        $tool->setLanguage($config->getLanguage());

        switch ($config->getMode()) {
            case 'agent':
                /** @var Agent $backup */
                $backup = $this->container->get(Agent::class);

                $loggers->use('app')->info('Backup is running in Agent mode');
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);

                $loggers->use('app')->info('Backup is running in Manager mode');
                break;
            default:
                throw new ConfigurationException(sprintf('The mode "%s" is invalid.', $config->getMode()));
        }

        $tool->mountDirectory($config->getTargetDirectory());

        return $backup;
    }
}
