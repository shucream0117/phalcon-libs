<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use Shucream0117\PhalconLib\Entities\Twitter\AbstractUser as AbstractTwitterUser;
use Shucream0117\PhalconLib\Entities\Twitter\AccessToken;
use Shucream0117\PhalconLib\Entities\Twitter\AccountSetting;
use Shucream0117\PhalconLib\Entities\Twitter\RequestToken;

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
     * この共通ライブラリではここは実装しないので、必要に応じて継承先でエンティティを定義する。
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
        $result = (array)$this->oauth->get('account/verify_credentials', [
            'include_email' => true,
            'skip_status' => true,
            'include_entities' => false,
        ]);
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
        $result = $this->oauth->get('account/settings');
        return new AccountSetting(
            $result->time_zone->tzinfo_name,
            $result->time_zone->utc_offset,
            $result->language
        );
    }

    /**
     * フォローしているユーザのID(not screen_name)を取得する。
     * 1回で最大5000件だが、それ以上は一旦無視することにするのでページングは考えない
     *
     * @param AccessToken $accessToken
     * @return string[]
     * @throws TwitterOAuthException
     */
    public function getFollowingIds(AccessToken $accessToken): array
    {
        $this->setAccessToken($accessToken);
        $result = $this->oauth->get('friends/ids', [
            'stringify_ids' => true,
            'count' => self::MAX_FOLLOWING_IDS_FETCH_COUNT,
        ]);
        return $result->ids ?? [];
    }
}
