<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Phalcon\Helper\Str;
use Psr\Http\Message\ResponseInterface;
use Shucream0117\PhalconLib\Constants\ContentType;
use Shucream0117\PhalconLib\Entities\Facebook\AccessToken;
use Shucream0117\PhalconLib\Entities\Facebook\AbstractUser as AbstractFacebookUser;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;
use Shucream0117\PhalconLib\Exceptions\OAuthException;
use Shucream0117\PhalconLib\Utils\Json;
use Symfony\Component\HttpFoundation\Response as StatusCode;

/**
 * 対応バージョン v6.0
 *
 * (公式SDKがv5系までの対応であるため自作)
 */
abstract class AbstractFacebookApiService extends AbstractService
{
    protected string $appId;
    protected string $appSecret;
    protected Client $client;

    protected const API_BASE_URL = 'https://graph.facebook.com/v6.0';

    protected const OAUTH_DIALOG_ENDPOINT = 'https://www.facebook.com/v6.0/dialog/oauth';

    /**
     * @see https://developers.facebook.com/docs/graph-api/using-graph-api/error-handling/#errorcodes
     */
    const RESPONSE_TYPE_CODE = 'code'; // auth code grant
    const RESPONSE_TYPE_TOKEN = 'token'; // implicit grant

    const TOKEN_TYPE_BEARER = 'Bearer';

    protected const ERROR_CODE_INVALID_TOKEN = 190;
    protected const ERROR_TYPE_OAUTH_EXCEPTION = 'OAuthException';


    /**
     * アプリケーションが要求するスコープ
     *
     * @see https://developers.facebook.com/docs/facebook-login/permissions/?locale=ja_JP
     * @return string[]
     */
    abstract protected static function getScopes(): array;

    /**
     * ユーザ情報を取得する際にほしいフィールド
     *
     * @see https://developers.facebook.com/docs/facebook-login/permissions/?locale=ja_JP#-------
     * @see https://developers.facebook.com/docs/graph-api/reference/user/#-
     * @return string[]
     */
    abstract protected static function getUserFields(): array;

    /**
     * ユーザ取得APIのレスポンスからFacebookUserオブジェクトを生成する。
     * パーミションや取得するフィールドによってユーザの構造体が変わるため、この共通ライブラリではここは実装しない。
     *
     * @param array $data
     * @return AbstractFacebookUser
     */
    abstract protected static function createUserFromApiResponse(array $data): AbstractFacebookUser;


    public function __construct(string $appId, string $appSecret, ?Client $client = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->client = is_null($client) ? new Client() : $client;
    }

    /**
     * Guzzleのオプション
     * @return array
     */
    protected static function getDefaultOptions(): array
    {
        return [
            'timeout' => 5,
            'headers' => [
                'Accept' => ContentType::JSON,
            ],
        ];
    }

    protected function getAuthorizationHeader(string $accessToken, string $type = self::TOKEN_TYPE_BEARER): array
    {
        return ['Authorization' => "{$type} {$accessToken}"];
    }

    /**
     * 認証ページのURLを生成する
     *
     * @param string $callbackUrl
     * @param string $state
     * @param string $responseType
     * @return string
     */
    public function getAuthorizePageUrl(
        string $callbackUrl,
        string $state,
        string $responseType = self::RESPONSE_TYPE_CODE
    ): string {
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $callbackUrl,
            'state' => $state,
            'response_type' => $responseType,
            'scope' => implode(',', static::getScopes()),
        ];
        return static::OAUTH_DIALOG_ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * テンポラリのコードとアクセストークンを引き換える
     *
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow?locale=ja_JP
     *
     * @param string $code
     * @param string $callbackUrl
     * @return AccessToken
     * @throws InvalidApiResponseFormatException
     * @throws OAuthException
     */
    public function getAccessTokenByCode(string $code, string $callbackUrl): AccessToken
    {
        $response = $this->get('/oauth/access_token', [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $callbackUrl,
            'code' => $code,
        ]);

        if ($response->getStatusCode() === StatusCode::HTTP_OK) {
            if ($data = $response->getBody()->getContents()) {
                $jsonArr = Json::decode($data);
                $token = $jsonArr['access_token'] ?? null;
                $tokenType = $jsonArr['token_type'] ?? null;
                $expiresIn = $jsonArr['expires_in'] ?? null;
                if (!$token || !$tokenType || !$expiresIn) {
                    throw new InvalidApiResponseFormatException();
                }
                return new AccessToken($token, $tokenType, $expiresIn);
            }
        }
        throw new OAuthException();
    }

    /**
     * アクセストークンの持ち主のユーザ情報を取得
     *
     * @param string $accessToken
     * @return AbstractFacebookUser
     * @throws OAuthException
     */
    public function getMe(string $accessToken): AbstractFacebookUser
    {
        $response = $this->get(
            '/me',
            ['fields' => implode(',', static::getUserFields())],
            $this->getAuthorizationHeader($accessToken)
        );
        if ($response->getStatusCode() !== StatusCode::HTTP_OK) {
            throw new OAuthException();
        }
        return static::createUserFromApiResponse(Json::decode($response->getBody()->getContents()));
    }

    /**
     * ID指定でユーザ情報を取得
     *
     * @param string $accessToken
     * @param string $userId
     * @return AbstractFacebookUser|null
     * @throws OAuthException
     */
    public function getUser(string $accessToken, string $userId): ?AbstractFacebookUser
    {
        $response = $this->get(
            "/{$userId}",
            ['fields' => implode(',', static::getUserFields())],
            $this->getAuthorizationHeader($accessToken)
        );
        $statusCode = $response->getStatusCode();
        if ($statusCode === StatusCode::HTTP_NOT_FOUND) {
            return null;
        }
        if ($statusCode !== StatusCode::HTTP_OK) {
            throw new OAuthException();
        }
        return static::createUserFromApiResponse(Json::decode($response->getBody()->getContents()));
    }

    /**
     * GETリクエスト
     *
     * @param string $path
     * @param array $queryParams
     * @param array $additionalHeaders
     * @return ResponseInterface
     */
    protected function get(string $path, array $queryParams = [], array $additionalHeaders = []): ResponseInterface
    {
        $options = [];
        if ($queryParams) {
            $options['query'] = $queryParams;
        }
        if ($additionalHeaders) {
            $options['headers'] = $additionalHeaders;
        }
        return $this->request('GET', $path, $options);
    }

    /**
     * Content-Type: application/json の POSTリクエスト
     *
     * @param string $path
     * @param array $params
     * @return ResponseInterface
     */
    protected function postJson(string $path, array $params = []): ResponseInterface
    {
        $options = [];
        if ($params) {
            $options['json'] = $params;
        }
        return $this->request('POST', $path, $options);
    }

    /**
     * 基本的には $this->get() や $this->postJson() などを使うこと。
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @return ResponseInterface
     * @throws InvalidAccessTokenException
     */
    protected function request(string $method, string $path, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request(
                $method,
                Str::concat('/', static::API_BASE_URL, $path),
                array_merge_recursive(static::getDefaultOptions(), $options)
            );
        } catch (RequestException $e) {
            if ($response = $e->getResponse()) {
                $this->checkErrorResponseIfTokenIsInvalidOrExpired($response);
                return $response;
            }
            throw $e;
        }
    }

    /**
     * トークン失効または不正なトークンであるエラーかどうかを確認し、その場合は例外をスローする
     * @param ResponseInterface $response
     */
    protected function checkErrorResponseIfTokenIsInvalidOrExpired(ResponseInterface $response): void
    {
        $errorCode = $this->getErrorCodeFromErrorResponse($response);
        if ($errorCode === static::ERROR_CODE_INVALID_TOKEN) {
            throw new InvalidAccessTokenException();
        }

        $errorType = $this->getErrorTypeFromErrorResponse($response);
        if ($errorType === static::ERROR_TYPE_OAUTH_EXCEPTION && is_null($errorCode)) {
            ;
            throw new InvalidAccessTokenException();
        }
    }

    /**
     * レスポンスからエラー種別を取得する
     *
     * @param ResponseInterface $response
     * @return string|null
     */
    protected function getErrorTypeFromErrorResponse(ResponseInterface $response): ?string
    {
        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();
        if ($content) {
            $jsonArr = Json::decode($content);
            return $jsonArr['error']['type'] ?? null;
        }
        return null;
    }

    /**
     * レスポンスからエラーコードを取得する
     *
     * @param ResponseInterface $response
     * @return int|null
     */
    protected function getErrorCodeFromErrorResponse(ResponseInterface $response): ?int
    {
        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();
        if ($content) {
            $jsonArr = Json::decode($content);
            return $jsonArr['error']['code'] ?? null;
        }
        return null;
    }
}
