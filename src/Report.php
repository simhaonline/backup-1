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
     * @var string[]
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
     * @var Compressible[]|Downloadable[]
     */
    private $logs;

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
        $this->recipients[] = compact('address', 'name', 'type');
    }

    /**
     * Add a report entry
     *
     * @param string $status
     * @param Compressible|Downloadable $model
     */
    public function add(string $status, object $model): void
    {
        $this->logs[][$status] = $model;
    }

    /**
     * Send the report
     *
     * @throws BackupException
     */
    public function send(): void
    {
        $sender = $this->sender['name'] ? "{$this->sender['name']} <{$this->sender['address']}>" : $this->sender['address'];

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
            $address = $recipient['name'] ? "{$recipient['name']} <{$recipient['address']}>" : $recipient['address'];

            switch ($recipient['type']) {
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
        foreach ($this->logs as $status => $model) {
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
        mail(
            implode(',', $to),
            '=?utf-8?B?' . base64_encode($this->subject) . '?=',
            $body,
            implode(PHP_EOL, $headers)
        );
    }
}
