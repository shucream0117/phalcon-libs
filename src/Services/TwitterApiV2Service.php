<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use RuntimeException;
use Shucream0117\PhalconLib\Entities\Twitter\AbstractUser as AbstractTwitterUser;
use Shucream0117\PhalconLib\Entities\Twitter\AccessToken;
use Shucream0117\PhalconLib\Entities\Twitter\Friendship;
use Shucream0117\PhalconLib\Entities\Twitter\RequestToken;
use Shucream0117\PhalconLib\Exceptions\TwitterApiErrorException;
use Shucream0117\PhalconLib\Utils\Json;

/**
 * this class supports just only Twitter API v2
 */
class TwitterApiV2Service extends AbstractService
{
    private TwitterOAuth $oauth;

    /*
     * エラーコードたち
     * @see https://developer.twitter.com/en/support/twitter-api/error-troubleshooting
     */
    const ERROR_CODE_RATE_LIMIT_EXCEEDED = 88;
    const ERROR_CODE_INVALID_OR_EXPIRED_TOKEN = 89;
    const ERROR_CODE_UNABLE_TO_VERIFY_CREDENTIALS = 99;
    const ERROR_CODE_OVER_CAPACITY = 130;
    const ERROR_CODE_INTERNAL_ERROR = 131;

    const MAX_FOLLOWING_IDS_FETCH_COUNT = 1000;

    /**
     * @var string User object を取得する API に渡すパラメーター
     * @see https://developer.twitter.com/en/docs/twitter-api/data-dictionary/object-model/user
     * @note v2 では profile_banner_url と email を取得する手段がなくなった
     */
    private const USER_FIELDS = 'id,username,name,description,profile_image_url,protected,entities';

    public function __construct(
        string $consumerKey,
        string $consumerSecret,
        ?string $oauthToken = null,
        ?string $oauthTokenSecret = null
    ) {
        $this->oauth = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
        $this->oauth->setApiVersion('2');
    }

    /**
     * レスポンスのUser objectからTwitterUserオブジェクトを生成する。
     *
     * @param array $data
     * @return AbstractTwitterUser
     */
    protected static function createFromUserResponse(array $data): AbstractTwitterUser
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
     * access token による user 取得。75req/15min per user
     * @param AccessToken $accessToken
     * @return AbstractTwitterUser
     * @throws TwitterApiErrorException
     */
    public function getMe(AccessToken $accessToken): AbstractTwitterUser
    {
        $this->setAccessToken($accessToken);
        $result = $this->get('users/me', [
            'user.fields' => self::USER_FIELDS,
        ]);
        return static::createFromUserResponse($result['data']);
    }

    /**
     * id による user 取得。900req/15min per user
     * @param AccessToken $accessToken
     * @param string $userId
     * @return AbstractTwitterUser
     * @throws TwitterApiErrorException
     */
    public function getUserById(AccessToken $accessToken, string $userId): AbstractTwitterUser
    {
        $this->setAccessToken($accessToken);
        $result = $this->get("users/{$userId}", [
            'user.fields' => self::USER_FIELDS,
        ]);
        return static::createFromUserResponse($result['data']);
    }

    /**
     * フォローしているユーザのID(not screen_name)を取得する。
     * 1回で最大1000件。15req/15min per user
     *
     * @param AccessToken $accessToken
     * @param string $userId
     * @param int $count
     * @param string|null $paginationToken next_token か previous_token のどちらかを渡す
     * @throws TwitterApiErrorException
     * @return array{ids: string[], result_count: int, next_token: string|null, previous_token: string|null}
     *     ids: ユーザのIDの配列
     *     result_count: 取得できたIDの数
     *     next_token: 次のページへ進むためのトークン
     *     previous_token: 前のページに戻るためのトークン
     */
    public function getFollowingIds(
        AccessToken $accessToken,
        string $userId,
        int $count = self::MAX_FOLLOWING_IDS_FETCH_COUNT,
        ?string $paginationToken = null
    ): array {
        $this->setAccessToken($accessToken);
        $params = [
            'max_results' => $count,
            'user.fields' => 'id',
        ];
        if ($paginationToken) {
            $params['pagination_token'] = $paginationToken;
        }
        $result = $this->get("users/{$userId}/following", $params);
        return [
            'ids' => array_map(fn(array $user) => $user['id'], $result['data']),
            'result_count' => $result['meta']['result_count'],
            'next_token' => $result['meta']['next_token'] ?? null,
            'previous_token' => $result['meta']['previous_token'] ?? null,
        ];
    }

    /**
     * ユーザーID(固定の数字ID)でフォローする
     * 50/15min per user
     *
     * @param AccessToken $accessToken
     * @param string $userId
     * @param string $targetUserId
     * @throws TwitterApiErrorException
     * @return array{following: bool, pending_follow: bool}
     *     following: フォローできたかどうか。鍵垢の場合はfalseになる。
     *     pending_follow: フォローリクエスト中かどうか。鍵垢の場合はtrueになる。
     */
    public function followByUserId(
        AccessToken $accessToken,
        string $userId,
        string $targetUserId
    ): array
    {
        $this->setAccessToken($accessToken);
        $result = $this->post("users/{$userId}/following", ['target_user_id' => $targetUserId], true);
        return [
            'following' => $result['data']['following'],
            'pending_follow' => $result['data']['pending_follow'],
        ];
    }

    /**
     * 関係性を取得。v2 では [COMING SOON] になっているためここでも未実装
     * @param string[] $userIds max 100 ids.
     * @return Friendship[]
     */
    public function getFriendshipsByUserIds(AccessToken $accessToken, array $userIds): array
    {
        throw new RuntimeException('Not implemented yet in Twitter API v2.');
    }

    /**
     * @param string $path
     * @param array<string, mixed> $parameters
     * @return array
     * @throws TwitterApiErrorException
     */
    protected function get(string $path, array $parameters = []): array
    {
        $result = $this->oauth->get($path, $parameters);
        $resultArr = Json::decode(Json::encode($result)); // 再帰的にキャストするために一度json文字列にしてから再度連想配列に戻す
        $this->handleErrorIfNeeded($resultArr);
        return $resultArr;
    }

    /**
     * @param string $path
     * @param array<string, mixed> $parameters
     * @param bool $json
     * @return array
     * @throws TwitterApiErrorException
     */
    protected function post(string $path, array $parameters = [], bool $json = false): array
    {
        $result = $this->oauth->post($path, $parameters, $json);
        $resultArr = Json::decode(Json::encode($result)); // 再帰的にキャストするために一度json文字列にしてから再度連想配列に戻す
        $this->handleErrorIfNeeded($resultArr);
        return $resultArr;
    }

    /**
     * @throws TwitterApiErrorException
     */
    protected function handleErrorIfNeeded(array $result): void
    {
        if (!empty($result['errors'])) {
            throw (new TwitterApiErrorException())->setErrors($result['errors']);
        }
    }
}
