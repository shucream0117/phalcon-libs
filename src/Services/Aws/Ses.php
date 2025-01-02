<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Aws;

use Aws\Result;
use Aws\Ses\SesClient;
use Shucream0117\PhalconLib\Services\EmailTransmitterInterface;

class Ses implements EmailTransmitterInterface
{
    private string $senderName;
    private string $senderEmailAddress;
    private SesClient $sesClient;

    protected const CHARSET = 'UTF-8';

    public function __construct(string $senderName, string $senderEmailAddress, SesClient $client)
    {
        $this->senderName = $senderName;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->sesClient = $client;
    }

    private function getSender(): string
    {
        $name = mb_encode_mimeheader($this->senderName, 'UTF-7', 'Q');
        return "{$name} <{$this->senderEmailAddress}>";
    }

    /**
     * メッセージ送信
     *
     * @param string[] $to
     * @param string $subject
     * @param string $body
     * @return Result
     */
    public function sendTextMessageToMany(array $to, string $subject, string $body): Result
    {
        return $this->sesClient->sendEmail([
            'Source' => $this->getSender(),
            'Destination' => [
                'ToAddresses' => $to,
            ],
            'Message' => [
                'Subject' => [
                    'Charset' => static::CHARSET,
                    'Data' => $subject,
                ],
                'Body' => [
                    'Text' => [
                        'Charset' => static::CHARSET,
                        'Data' => $body,
                    ],
                ],
            ],
        ]);
    }

    public function sendTextMessage(string $to, string $subject, string $body): Result
    {
        return $this->sendTextMessageToMany([$to], $subject, $body);
    }
}
