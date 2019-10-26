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

use Monolog\Formatter\LineFormatter;
use Monolog\Logger as MonologLogger;

/**
 * Class Logger
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Logger
{

    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    /**
     * @var array
     */
    private $loggers = [];

    /**
     * Logger constructor
     */
    public function __construct()
    {
        # Set default line formatter
        $this->lineFormatter = new LineFormatter(null, 'Y-m-d H:i:s');
    }

    /**
     * Set a logger
     *
     * @param MonologLogger $logger
     */
    public function set(MonologLogger $logger): void
    {
        $this->loggers[$logger->getName()] = $logger;
    }

    /**
     * Use a logger
     *
     * @param string $name
     * @return MonologLogger
     */
    public function use(string $name): MonologLogger
    {
        return $this->loggers[$name];
    }

    /**
     * Get line formatter
     *
     * @return LineFormatter
     */
    public function getLineFormatter(): LineFormatter
    {
        return $this->lineFormatter;
    }
}
