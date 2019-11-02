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

/**
 * Class Report
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Report
{

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var mixed[]
     */
    private $recipients;

    /**
     * Add sender
     *
     * @param string      $address
     * @param string|null $name
     */
    public function setSender(string $address, string $name = null): void
    {
        $this->sender = $name ? "{$name} <{$address}>" : $address;
    }

    /**
     * Add subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Add recipient
     *
     * @param string      $address
     * @param string|null $name
     * @param string|null $type
     */
    public function addRecipient(string $address, string $name = null, string $type = null): void
    {
        $this->recipients[] = [
          'address' => $address,
          'name'    => $name,
          'type'    => $type
        ];
    }

    /**
     * Send the report
     */
    public function send(): void
    {

    }
}
