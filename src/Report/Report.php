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
            'X-Application: Backup'
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

        $report = '';
        foreach ($this->entries as $entry) {
            switch ($entry['status']) {
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

            $report .= <<<out
<tr>
    <td>{$entry['type']}</td>
    <td>{$entry['model']->getName()}</td>
    <td>{$entry['message']}</td>
    <td style="background-color:{$backgroundColor};text-align:center;">{$entry['status']}</td>
</tr>
out;
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
}
