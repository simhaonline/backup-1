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

namespace Backup\Tests;

use Backup\Database;
use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseTest
 *
 * @package Backup\Tests
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DatabaseTest extends TestCase
{

    /**
     * Test dump command
     *
     * @dataProvider dataDumpCmd
     *
     * @param Database $database
     * @param string $cmd
     */
    public function testDumpCmd(Database $database, string $cmd): void
    {
        self::assertEquals($cmd, $database->createDumpCmd());
    }

    /**
     * Data dump command
     *
     * ATTENTION: This test only works on LINUX because of the PHP function "escapeshellarg" behavior.
     *
     * @return array
     */
    public function dataDumpCmd(): array
    {
        # Docker container database
        $config = [
            'name' => '',
            'source' => [
                'type' => 'docker',
                'container' => 'app-db'
            ],
            'target' => null
        ];

        $database = new Database($config);
        $database->setSource('/tmp/app-db.sql');

        $cmd = 'docker exec \'app-db\' sh -c \'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE\' > \'/tmp/app-db.sql\'';

        $return[] = [$database, $cmd];

        # Local database
        $config = [
            'name' => '',
            'source' => [
                'type' => null,
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'My+Very_Secret-Password.'
            ],
            'target' => null
        ];

        $database = new Database($config);
        $database->setSource('/tmp/db.sql');

        $cmd = 'mysqldump -h\'localhost\' -u\'root\' -p"My+Very_Secret-Password." --databases `mysql --skip-column-names -e "SELECT GROUP_CONCAT(schema_name SEPARATOR \' \') FROM information_schema.schemata WHERE schema_name NOT IN (\'information_schema\',\'mysql\',\'performance_schema\');"` > \'/tmp/db.sql\'';

        $return[] = [$database, $cmd];

        return $return;
    }
}
