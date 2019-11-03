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

namespace Backup\Tests;

use Backup\Manager\Interfaces\Downloadable;
use Backup\Manager\Model\ServerModel;
use Backup\Manager\Service\DownloadService;
use PHPUnit\Framework\TestCase;

/**
 * Class DownloadTest
 *
 * @package Backup\Tests
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DownloadTest extends TestCase
{

    /**
     * Test download command
     *
     * @dataProvider dataDownloadCmd
     *
     * @param Downloadable $download
     * @param string $cmd
     */
    public function testDownloadCmd(Downloadable $download, string $cmd): void
    {
        self::assertEquals($cmd, (new DownloadService())->getCmd($download));
    }

    /**
     * Data download command
     *
     * @return array[]
     */
    public function dataDownloadCmd(): array
    {
        $download = new ServerModel([
            'name' => '',
            'host' => '127.0.0.1',
            'source' => '/backup',
            'target' => '/backup/My/Target/Folder No-1',
            'ssh' => [
                'port' => 2222,
                'user' => 'backupuser',
                'key' => '/root/.ssh/id_rsa'
            ]
        ]);

        $cmd = 'rsync -r -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p 2222 -i \'/root/.ssh/id_rsa\'" backupuser@127.0.0.1:\'/backup/\' \'/backup/My/Target/Folder No-1/\'';

        return [
            [$download, $cmd]
        ];
    }
}
