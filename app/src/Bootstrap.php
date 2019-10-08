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
use Vection\Component\DI\Container;

/**
 * Class Bootstrap
 *
 * @author BloodhunterD
 *
 * @package BackupAgent
 */
class Bootstrap
{

    /**
     * @var Container
     */
    public $container;

    /**
     * Bootstrap constructor.
     *
     * @throws ConfigurationException
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->registerNamespace([
            'BackupAgent',
        ]);

        $config = new Configuration();
        $config->mount();
        $config->load();

        $this->container->add($config);
    }
}
