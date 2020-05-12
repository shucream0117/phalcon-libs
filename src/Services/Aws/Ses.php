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

    public function __construct(string $senderName, string $senderEmailAddress, SesClient $client)
    {
        $this->senderName = $senderName;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->sesClient = $client;
    }

    private function getSender(): string
    {
        return "{$this->senderName} <{$this->senderEmailAddress}>";
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
                    'Data' => $subject,
                ],
                'Body' => [
                    'Text' => [
                        'Data' => $body,
                    ],
                ],
            ],
        ]);
    }

    public function sendTextMessage(string $to, string $subject, string $body)
    {
        return $this->sendTextMessageToMany([$to], $subject, $body);
    }
}
