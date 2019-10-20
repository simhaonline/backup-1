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
     * @return bool
     */
    public function createDirectory(string $path): bool
    {
        $absolutePath = $this->config->getTargetDirectory() . $path;

        if (!is_dir($absolutePath)) {
            $cmd = sprintf('mkdir -p %s', escapeshellarg($absolutePath));

            $r = $this->execute($cmd);

            if (!$r && !is_dir($absolutePath)) {
                return false;
            }
        }

        $this->logger->use('app')->debug(sprintf('Directory "%s" successfully created', $path));

        return true;
    }

    /**
     * Execute a command
     *
     * @param string $command
     *
     * @return bool
     */
    public function execute(string $command): bool
    {
        exec($command, $output, $return);

        unset($output);

        # The command failed, if it returns a non-zero value
        if (! (bool) $return) {
            $this->logger->use('app')->debug(sprintf('Command successfully executed: %s', $command));

            return true;
        }

        return false;
    }
}
