<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use Shucream0117\PhalconLib\Entities\Twitter\AbstractUser as AbstractTwitterUser;
use Shucream0117\PhalconLib\Entities\Twitter\AccessToken;
use Shucream0117\PhalconLib\Entities\Twitter\AccountSetting;
use Shucream0117\PhalconLib\Entities\Twitter\RequestToken;
use Shucream0117\PhalconLib\Exceptions\OAuthException;
use Shucream0117\PhalconLib\Exceptions\TwitterApiErrorException;
use Shucream0117\PhalconLib\Utils\Json;

/*
 * this class supports just only Twitter API v1.1
 */

class TwitterApiService extends AbstractService
{
    private TwitterOAuth $oauth;

    const MAX_FOLLOWING_IDS_FETCH_COUNT = 5000;

    public function __construct(
        string $consumerKey,
        string $consumerSecret,
        ?string $oauthToken = null,
        ?string $oauthTokenSecret = null
    ) {
        $this->oauth = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
        $this->oauth->setApiVersion('1.1');
    }

    /**
     * /verify_credentials のレスポンスからTwitterUserオブジェクトを生成する。
     *
     * @param array $data
     * @return AbstractTwitterUser
     */
    protected static function createFromCredentialResponse(array $data): AbstractTwitterUser
    {
        return new class($data['id']) extends AbstractTwitterUser {
            public function __construct(string $id)
            {
                $this->id = $id;
            }
        };
    }

    public function setAccessToken(AccessToken $token): self
    {
        $this->oauth->setOauthToken($token->getToken(), $token->getSecret());
        return $this;
    }

    /**
     * @param string $callbackUrl
     * @return RequestToken
     * @throws TwitterOAuthException
     */
    public function getRequestToken(string $callbackUrl): RequestToken
    {
        $result = $this->oauth->oauth('oauth/request_token', ['oauth_callback' => $callbackUrl]);
        return new RequestToken($result['oauth_token'], $result['oauth_token_secret']);
    }

    /**
     * @param RequestToken $requestToken
     * @return string
     */
    public function getAuthorizeUrl(RequestToken $requestToken): string
    {
        return $this->oauth->url('oauth/authorize', ['oauth_token' => $requestToken->getToken()]);
    }

    /**
     * @param RequestToken $requestToken
     * @param string $oauthVerifier
     * @return AccessToken
     * @throws TwitterOAuthException
     */
    public function getAccessToken(RequestToken $requestToken, string $oauthVerifier): AccessToken
    {
        $this->oauth->setOauthToken($requestToken->getToken(), $requestToken->getSecret());
        $result = $this->oauth->oauth('oauth/access_token', ['oauth_verifier' => $oauthVerifier]);
        return new AccessToken(
            $result['oauth_token'],
            $result['oauth_token_secret']
        );
    }

    /**
     * @param AccessToken $accessToken
     * @return AbstractTwitterUser
     * @throws TwitterOAuthException
     */
    public function verifyCredentials(AccessToken $accessToken): AbstractTwitterUser
    {
        $this->setAccessToken($accessToken);
        $result = $this->get('account/verify_credentials', [
            'include_email' => true,
            'skip_status' => true,
            'include_entities' => false,
        ]);
        // 再帰的にarrayにキャストするために横着してJsonへのエンコードとデコードを行き来しています
        return static::createFromCredentialResponse($result);
    }

    /**
     * @param AccessToken $accessToken
     * @param string $userId
     * @return AbstractTwitterUser
     * @throws OAuthException
     * @throws TwitterApiErrorException
     */
    public function getUserById(AccessToken $accessToken, string $userId): AbstractTwitterUser
    {
        $this->setAccessToken($accessToken);
        $result = $this->get('users/show', ['user_id' => $userId]);
        // 再帰的にarrayにキャストするために横着してJsonへのエンコードとデコードを行き来しています
        return static::createFromCredentialResponse($result);
    }

    /**
     * @param AccessToken $accessToken
     * @return AccountSetting
     * @throws TwitterOAuthException
     */
    public function getAccountSettings(AccessToken $accessToken): AccountSetting
    {
        $this->setAccessToken($accessToken);
        $result = $this->get('account/settings');
        return new AccountSetting(
            $result['time_zone']['tzinfo_name'],
            $result['time_zone']['utc_offset'],
            $result['language']
        );
    }

    /**
     * フォローしているユーザのID(not screen_name)を取得する。
     * 1回で最大5000件。
     *
     * @param AccessToken $accessToken
     * @param int $count
     * @param string|null $cursor
     * @return array
     */
    public function getFollowingIds(
        AccessToken $accessToken,
        int $count = self::MAX_FOLLOWING_IDS_FETCH_COUNT,
        ?string $cursor = null
    ): array {
        $this->setAccessToken($accessToken);
        $params = [
            'stringify_ids' => true,
            'count' => $count,
        ];
        if (!is_null($cursor)) {
            $params['cursor'] = $cursor;
        }

        $result = $this->get('friends/ids', $params);
        return [
            'ids' => $result['ids'] ?? [],
            'previous_cursor' => $result['previous_cursor_str'] ?? null,
            'next_cursor' => $result['next_cursor_str'] ?? null,
        ];
    }

    /**
     * ユーザーID(固定の数字ID)でフォローする
     * 400/24h per user
     *
     * @param AccessToken $accessToken
     * @param string $targetUserId
     * @param bool $enableNotification
     * @throws OAuthException
     * @throws TwitterApiErrorException
     */
    public function followByUserId(
        AccessToken $accessToken,
        string $targetUserId,
        bool $enableNotification = false // 通知登録
    ): void {
        $this->setAccessToken($accessToken);
        $this->post('friendships/create', ['user_id' => $targetUserId, 'follow' => $enableNotification]);
    }

    /**
     * @param string $path
     * @param array<string, mixed> $parameters
     * @return array
     * @throws OAuthException
     * @throws TwitterApiErrorException
     */
    protected function get(string $path, array $parameters = []): array
    {
        $result = $this->oauth->get($path, $parameters);
        if (empty($result)) {
            throw new OAuthException();
        }
        $resultArr = Json::decode(Json::encode($result)); // 再帰的にキャストするために一度json文字列にしてから再度連想配列に戻す
        $this->handleErrorIfNeeded($resultArr);
        return $resultArr;
    }

    /**
     * @param string $path
     * @param array<string, mixed> $parameters
     * @return array
     * @throws OAuthException
     * @throws TwitterApiErrorException
     */
    protected function post(string $path, array $parameters = []): array
    {
        $result = $this->oauth->post($path, $parameters);
        if (empty($result)) {
            throw new OAuthException();
        }
        $resultArr = Json::decode(Json::encode($result)); // 再帰的にキャストするために一度json文字列にしてから再度連想配列に戻す
        $this->handleErrorIfNeeded($resultArr);
        return $resultArr;
    }

    protected function handleErrorIfNeeded(array $result): void
    {
        if (!empty($result['errors'])) {
            throw (new TwitterApiErrorException())->setErrors($result['errors']);
        }
    }
}
