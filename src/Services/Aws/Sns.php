<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Aws;

use Aws\Result;
use Aws\Sns\SnsClient;
use Shucream0117\PhalconLib\Entities\PhoneNumber;
use Shucream0117\PhalconLib\Services\SmsTransmitterInterface;

class Sns implements SmsTransmitterInterface
{
    private string $senderId;
    private SnsClient $client;

    public function __construct(string $senderId, SnsClient $client)
    {
        $this->senderId = $senderId;
        $this->client = $client;
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @param string $message
     * @return Result
     */
    public function send(PhoneNumber $phoneNumber, string $message): Result
    {
        return $this->client->publish([
            'MessageAttributes' => [
                'AWS.SNS.SMS.SenderID' => [
                    'DataType' => 'String',
                    'StringValue' => $this->senderId,
                ],
                'AWS.SNS.SMS.SMSType' => [
                    'DataType' => 'String',
                    'StringValue' => 'Transactional',
                ]
            ],
            'Message' => $message,
            'PhoneNumber' => $phoneNumber->getFullyQualifiedPhoneNumber(),
        ]);
    }
}
