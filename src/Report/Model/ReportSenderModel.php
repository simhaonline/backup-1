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

namespace Backup\Report\Model;

/**
 * Class Report Sender Model
 *
 * @package Backup\Report\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class ReportSenderModel
{

    /**
     * @var string
     */
    private $address = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * Report Sender Model constructor
     *
     * @param string[] $sender
     */
    public function __construct(array $sender)
    {
        # Optional
        $this->setAddress($sender['address'] ?? $this->address);
        $this->setName($sender['name'] ?? $this->name);
    }

    /**
     * Set the address
     *
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * Get the address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
