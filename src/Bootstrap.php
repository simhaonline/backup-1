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
use Locale;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Container;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Bootstrap
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Bootstrap
{

    use AnnotationInjection;

    /**
     * @var Container
     */
    private $container;

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
        # Initialize application logging
        $appLogger = new MonologLogger('app');
        $appLogger->pushHandler(new StreamHandler('/var/log/backup.log'));
        $appLogger->pushHandler(new PHPConsoleHandler());

        # Initialize report logging
        $reportLogger = new MonologLogger('report');
        $reportLogger->pushHandler(new StreamHandler(ROOT_DIR . DIRECTORY_SEPARATOR . 'backup-report.log'));

        # Wrap loggers to be able to inject
        $loggers = new Logger();
        $loggers->set($appLogger);
        $loggers->set($reportLogger);

        # Make logger injectable
        $this->container->add($loggers);

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);
        $config->mount();
        $config->load();

        $this->setTimezone($config->getTimezone());
        $this->setLanguage($config->getLanguage());

        switch ($config->getMode()) {
            case 'agent':
                /** @var Agent $backup */
                $backup = $this->container->get(Agent::class);

                $this->logger->use('app')->info('Backup is running in Agent mode');
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);

                $this->logger->use('app')->info('Backup is running in Manager mode');
                break;
            default:
                throw new ConfigurationException(sprintf('The mode "%s" is invalid.', $config->getMode()));
        }

        $this->tool->mountDirectory($config->getTargetDirectory());

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

        $this->logger->use('app')->debug('Timezone successfully set');
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

        $this->logger->use('app')->debug('Language successfully set');
    }
}
