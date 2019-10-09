<?php
/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * © BloodhunterD <backup@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Backup;

/**
 * Class Report
 *
 * @author BloodhunterD
 *
 * @package Backup
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
     * @var array
     */
    private $recipients;

    /**
     * Add sender
     *
     * @param string      $mail
     * @param string|null $address
     */
    public function setSender(string $address, string $name = null): void
    {
        $this->sender = $name ? "{$name} <{$address}>" : $address;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
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
     * Get subject
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Add recipient
     *
     * @param string $address
     */
    public function addRecipient(string $address): void
    {
        $this->recipients[] = $address;
    }

    /**
     * Get recipients
     *
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
