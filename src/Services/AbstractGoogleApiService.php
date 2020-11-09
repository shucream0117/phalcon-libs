<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Google_Client;
use Google_Exception;
use Shucream0117\PhalconLib\Entities\Google\AbstractUser as AbstractGoogleUser;
use Shucream0117\PhalconLib\Entities\Google\AccessToken;
use Shucream0117\PhalconLib\Exceptions\OAuthException;

/**
 * @deprecated \Shucream0117\PhalconLib\Services\Google の方を使う
 */
abstract class AbstractGoogleApiService extends AbstractService
{
    private Google_Client $client;

    const SCOPE_USER_INFO_PROFILE = 'https://www.googleapis.com/auth/userinfo.profile';
    const SCOPE_USER_INFO_EMAIL = 'https://www.googleapis.com/auth/userinfo.email';

    const ACCESS_TYPE_ONLINE = 'online';
    const ACCESS_TYPE_OFFLINE = 'offline';

    const PROMPT_NONE = 'none';
    const PROMPT_CONSENT = 'consent';
    const PROMPT_SELECT_ACCOUNT = 'select_account';

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    public function setClient(Google_Client $client)
    {
        $this->client = $client;
    }

    public function setAccessToken(AccessToken $token): self
    {
        $this->client->setAccessToken($token->getRaw());
        return $this;
    }

    /**
     * 認証ページのURLをｓ湯特
     * @param string[] $scope
     * @param string $accessType
     * @param string $prompt
     * @return string
     */
    public function getAuthorizeUrl(
        array $scope = [self::SCOPE_USER_INFO_PROFILE, self::SCOPE_USER_INFO_EMAIL],
        string $accessType = self::ACCESS_TYPE_ONLINE,
        string $prompt = self::PROMPT_CONSENT
    ): string {
        $this->client->addScope($scope);
        $this->client->setAccessType($accessType);
        $this->client->setPrompt($prompt);
        return $this->client->createAuthUrl();
    }

    /**
     * テンポラリコードとAccessTokenを引き換える
     *
     * @param string $code
     * @return AccessToken
     * @throws Google_Exception
     * @throws OAuthException
     */
    public function getAccessToken(string $code): AccessToken
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (!empty($token['error'])) {
            throw new OAuthException();
        }
        return new AccessToken($token);
    }

    /**
     * アクセストークンに紐づくユーザ情報を取得する
     *
     * @param AccessToken $accessToken
     * @return AbstractGoogleUser
     * @throws Google_Exception
     */
    public function verifyIdToken(AccessToken $accessToken): AbstractGoogleUser
    {
        $this->setAccessToken($accessToken);
        return static::createFromCredentialResponseObject($this->client->verifyIdToken());
    }


    /**
     * verifyIdToken のレスポンスからGoogleUserオブジェクトを生成する。
     * この共通ライブラリではGoogleUserの実装について考えないので、抽象メソッドになっている。
     *
     * @param array $data
     * @return AbstractGoogleUser
     */
    abstract protected static function createFromCredentialResponseObject(array $data): AbstractGoogleUser;
}
