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

use Backup\Exception\BackupException;
use Backup\Interfaces\Compressible;
use Backup\Interfaces\Downloadable;
use Backup\Model\ReportRecipientModel;
use Backup\Model\ReportSenderModel;

/**
 * Class Report
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Report
{

    private const MAIL_TO = 'to';
    private const MAIL_CC = 'cc';
    private const MAIL_BCC = 'bcc';

    public const RESULT_OK = 'ok';
    public const RESULT_WARNING = 'warning';
    public const RESULT_ERROR = 'error';

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
     * @var Compressible[]|Downloadable[]
     */
    private $entries;

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
     * @param Compressible|Downloadable $model
     */
    public function add(string $status, object $model): void
    {
        $this->entries[][$status] = $model;
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
        foreach ($this->entries as $status => $model) {
            switch ($status) {
                case self::RESULT_OK:
                    $color = '#4CAF50';
                    break;
                case self::RESULT_WARNING:
                    $color = '#F4AF50';
                    break;
                case self::RESULT_ERROR:
                    $color = '#F44336';
                    break;
                default:
                    $color = '#CCCCCC';
            }

            $report .= <<<out
<tr>
    <td>{$model->getName()}</td>
    <td style="text-align:center;background-color:{$color};">{$status}</td>
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
