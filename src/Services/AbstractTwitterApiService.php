<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use Shucream0117\PhalconLib\Entities\Twitter\AbstractUser as AbstractTwitterUser;
use Shucream0117\PhalconLib\Entities\Twitter\AccessToken;
use Shucream0117\PhalconLib\Entities\Twitter\AccountSetting;
use Shucream0117\PhalconLib\Entities\Twitter\RequestToken;
use Shucream0117\PhalconLib\Utils\Json;

abstract class AbstractTwitterApiService extends AbstractService
{
    private TwitterOAuth $oauth;

    const MAX_FOLLOWING_IDS_FETCH_COUNT = 5000;

    public function __construct(TwitterOAuth $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * /verify_credentials のレスポンスからTwitterUserオブジェクトを生成する。
     *
     * @param array $data
     * @return AbstractTwitterUser
     */
    abstract protected static function createFromCredentialResponse(array $data): AbstractTwitterUser;

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
            $accessToken = $result['oauth_token'],
            $accessTokenSecret = $result['oauth_token_secret']
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
        $result = $this->oauth->get('account/verify_credentials', [
            'include_email' => true,
            'skip_status' => true,
            'include_entities' => false,
        ]);
        // 再帰的にarrayにキャストするために横着してJsonへのエンコードとデコードを行き来しています
        return static::createFromCredentialResponse($this->castToArray($result));
    }

    /**
     * @param AccessToken $accessToken
     * @return AccountSetting
     * @throws TwitterOAuthException
     */
    public function getAccountSettings(AccessToken $accessToken): AccountSetting
    {
        $this->setAccessToken($accessToken);
        $result = $this->oauth->get('account/settings');
        return new AccountSetting(
            $result->time_zone->tzinfo_name,
            $result->time_zone->utc_offset,
            $result->language
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

        $result = $this->oauth->get('friends/ids', $params);
        $result = $this->castToArray($result);
        return [
            'ids' => $result['ids'] ?? [],
            'previous_cursor' => $result['previous_cursor_str'] ?? null,
            'next_cursor' => $result['next_cursor_str'] ?? null,
        ];
    }

    private function castToArrayRecursively(\stdClass $stdClass): array
    {
        // 再帰的にarrayにキャストするために横着してJsonへのエンコードとデコードを行き来しています
        return Json::decode(Json::encode($stdClass));
    }
}
