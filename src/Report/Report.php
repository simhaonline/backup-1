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

namespace Backup\Report;

use Backup\Exception\BackupException;
use Backup\Interfaces\Backup;
use Backup\Report\Model\ReportRecipientModel;
use Backup\Report\Model\ReportSenderModel;

/**
 * Class Report
 *
 * @package Backup\Report
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Report
{
    private const MAIL_TO = 'to';
    private const MAIL_CC = 'cc';
    private const MAIL_BCC = 'bcc';

    public const RESULT_OK = 'OK';
    public const RESULT_INFO = 'INFO';
    public const RESULT_WARNING = 'WARNING';
    public const RESULT_ERROR = 'ERROR';

    /**
     * @var ReportSenderModel
     */
    private $sender;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var ReportRecipientModel[]
     */
    private $recipients;

    /**
     * @var mixed[]
     */
    private $entries = [];

    /**
     * Set the sender
     *
     * @param ReportSenderModel $sender
     */
    public function setSender(ReportSenderModel $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * Set the subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Add a recipient
     *
     * @param ReportRecipientModel $recipient
     */
    public function addRecipient(ReportRecipientModel $recipient): void
    {
        $this->recipients[] = $recipient;
    }

    /**
     * Add a report entry
     *
     * @param string $status
     * @param string $type
     * @param object $model
     * @param string $message
     */
    public function add(string $status, string $type, object $model, string $message = ''): void
    {
        $this->entries[] = compact('status', 'type', 'model', 'message');
    }

    /**
     * Send the report
     *
     * @throws BackupException
     */
    public function send(): bool
    {
        $sender = $this->sender->getName() ? "{$this->sender->getName()} <{$this->sender->getAddress()}>" : $this->sender->getAddress();

        $headers = [
            'From: ' . $sender,
            'Reply-To: ' . $sender,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Application: Backup Report'
        ];

        $to = [];
        $cc = [];
        $bcc = [];
        foreach ($this->recipients as $recipient) {
            $address = $recipient->getName() ? "{$recipient->getName()} <{$recipient->getAddress()}>" : $recipient->getAddress();

            switch ($recipient->getType()) {
                default:
                case self::MAIL_TO:
                    $to[] = $address;
                    break;
                case self::MAIL_CC:
                    $cc[] = $address;
                    break;
                case self::MAIL_BCC:
                    $bcc[] = $address;
                    break;
            }
        }

        if ($cc) {
            $headers[] = 'Cc: ' . implode($cc);
        }

        if ($bcc) {
            $headers[] = 'Bcc: ' . implode($bcc);
        }

        $types = [
            Backup::TYPE_DIRECTORY,
            Backup::TYPE_DATABASE,
            Backup::TYPE_SERVER
        ];

        foreach ($types as $type) {
            $entries[$type] = array_filter($this->entries, static function ($entry) use ($type) {
                return $entry['type'] === $type;
            });
        }

        $report = '';
        foreach ($entries as $type => $entry) {
            $report .= <<<report
            <tr>
                <td>
                    {$this->getEmoji($type)}
                </td>
                <td colspan="2" style="font-weight:bold;">
                    {$type}
                </td>
            </tr>
            report;

            foreach ($entry as $e) {
                $report .= <<<report
                <tr>
                    <td style="font-weight:bold;
                               background-color:{$this->getBackgroundColor($entry['status'])};
                               text-align:center;">
                        {$e['status']}
                    </td>
                    <td>
                        {$e['model']->getName()}
                    </td>
                    <td>
                        {$e['message']}
                    </td>
                </tr>
                report;
            }
        }

        $template = file_get_contents(RES_DIR . DIRECTORY_SEPARATOR . 'report.html');

        if ($template === false) {
            throw new BackupException('Failed to load the report mail template.');
        }

        $body = str_replace(['###date###', '###report###'], [strftime('%x %X'), $report], $template);

        # The subject is a header and headers are only allowed to contain ASCII chars,
        # so we need to encode the subject like described in RFC 1342
        return mail(
            implode(',', $to),
            '=?utf-8?B?' . base64_encode($this->subject) . '?=',
            $body,
            implode(PHP_EOL, $headers)
        );
    }

    /**
     * Get background color by status
     *
     * @param string $status
     * @return string
     */
    private function getBackgroundColor(string $status): string
    {
        switch ($status) {
            case self::RESULT_INFO:
                $backgroundColor = '#2962FF';
                break;
            case self::RESULT_OK:
                $backgroundColor = '#00C853';
                break;
            case self::RESULT_WARNING:
                $backgroundColor = '#FFAB00';
                break;
            case self::RESULT_ERROR:
                $backgroundColor = '#D50000';
                break;
            default:
                $backgroundColor = '#212121';
        }

        return $backgroundColor;
    }

    /**
     * Get emoji by type
     *
     * @param string $type
     * @return string
     */
    private function getEmoji(string $type): string
    {
        // Replace type by emoji
        switch ($type) {
            case Backup::TYPE_DIRECTORY:
                $emoji = '\xF0\x9F\x93\x81';
                break;
            case Backup::TYPE_DATABASE:
                $emoji = '\xF0\x9F\x92\xBF';
                break;
            case Backup::TYPE_SERVER:
                $emoji = '\xF0\x9F\x92\xBB';
                break;
            default:
                $emoji = '\xE2\x9D\x93';
        }

        return $emoji;
    }
}
