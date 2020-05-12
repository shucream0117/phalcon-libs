<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Firebase;

use GuzzleHttp\Client;
use Shucream0117\PhalconLib\Constants\ContentType;
use Shucream0117\PhalconLib\Entities\Firebase\Payload;
use Shucream0117\PhalconLib\Exceptions\FcmTokenExpiredException;
use Shucream0117\PhalconLib\Utils\Json;

class Fcm
{
    protected string $apiKey;
    protected Client $httpClient;

    /**
     * @see https://firebase.google.com/docs/cloud-messaging/http-server-ref?hl=ja
     */
    const FCM_LEGACY_API_ENDPOINT = 'https://fcm.googleapis.com/fcm/send';
    const FCM_ERROR_INVALID_REGISTRATION = 'InvalidRegistration';
    const FCM_ERROR_NOT_REGISTERED = 'NotRegistered';

    const MAX_COUNT_PER_EXECUTION = 1000; // 一回ごとの最大送信件数

    public function __construct(string $apiKey, Client $client)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $client;
    }

    /**
     * 単体送信(複数送信のラッパー)
     *
     * @param string $token
     * @param Payload $payload
     * @throws FcmTokenExpiredException
     */
    public function send(string $token, Payload $payload): void
    {
        if ($result = $this->sendMany([$token], $payload)) {
            throw new FcmTokenExpiredException();
        }
    }

    /**
     * 複数送信を行い、失効しているトークンのリストを返却する。
     *
     * @param string[] $tokens
     * @param Payload $payload
     * @return string[]
     */
    public function sendMany(array $tokens, Payload $payload): array
    {
        if (!$tokens) {
            return [];
        }

        /** @var string[] $expiredTokens */
        $expiredTokens = [];

        $offset = 0;
        $length = static::MAX_COUNT_PER_EXECUTION;

        /** @var string[] $sliced */
        while ($sliced = array_slice($tokens, $offset, $length, true)) {
            $offset += $length;
            $response = $this->httpClient->post(self::FCM_LEGACY_API_ENDPOINT, [
                'headers' => [
                    'Authorization' => "key={$this->apiKey}",
                    'Content-Type' => ContentType::JSON,
                ],
                'json' => [
                    'registration_ids' => $tokens,
                    'priority' => 'high',
                    'content_available' => true,
                    'data' => $payload->toArray(),
                ],
            ]);
            $resArr = Json::decode($response->getBody()->getContents());
            $results = $resArr['results'] ?? [];
            for ($i = 0; $i < count($results); $i++) {
                if (!$error = ($results[$i]['error'] ?? null)) {
                    continue;
                }
                // 無効なトークンがある場合はそれを通知するために返却するリストに入れる
                $errorCodes = [self::FCM_ERROR_INVALID_REGISTRATION, self::FCM_ERROR_NOT_REGISTERED];
                if (in_array($error, $errorCodes)) {
                    $expiredTokens[] = $sliced[$i];
                    continue;
                }
            }
        }
        return $expiredTokens;
    }
}
