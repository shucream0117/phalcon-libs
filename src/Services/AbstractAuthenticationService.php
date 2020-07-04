<?php

declare(strict_types=1);

namespace ScannerAppApi\Services;

use DateTime;
use Phalcon\Storage\Adapter\AdapterInterface;
use Shucream0117\PhalconLib\Entities\AuthenticationKey;
use Shucream0117\PhalconLib\Models\Loginable;
use Shucream0117\PhalconLib\Services\AbstractService;
use Shucream0117\PhalconLib\Utils\Date;

abstract class AbstractAuthenticationService extends AbstractService
{
    protected AdapterInterface $storage;

    /**
     * ユーザの認証キーを格納するためのRedisのキー
     * @param string $authKey
     * @return string
     */
    abstract protected function createAuthKeyStorageKey(string $authKey): string;

    /**
     * キーの生存時間を返す
     * @return int
     */
    abstract protected function getAuthKeyTtl(): int;

    /**
     * 認証キーから対象アカウントを返す
     * @param string $authKey
     * @return Loginable|null
     */
    abstract public function getByAuthKey(string $authKey): ?Loginable;

    /**
     * 認証キーを生成する。
     *
     * @param Loginable $user
     * @return string
     */
    abstract protected function createKey(Loginable $user): string;

    public function __construct(AdapterInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * 認証キーを作成し、Redisに入れる
     *
     * @param Loginable $user
     * @param DateTime|null $now
     * @return AuthenticationKey
     */
    public function createAuthKey(Loginable $user, ?DateTime $now = null): AuthenticationKey
    {
        $key = $this->createKey($user);
        $this->storage->set(
            $this->createAuthKeyStorageKey($key),
            $user->getId(),
            $this->getAuthKeyTtl()
        );
        if (!$now) {
            $now = Date::createDateTime();
        }
        return new AuthenticationKey($key, $now->getTimestamp() + $this->getAuthKeyTtl());
    }

    /**
     * 認証キーから、対象アカウントの識別子を取得する
     *
     * @param string $authKey
     * @return string|null
     */
    public function getIdByAuthKey(string $authKey): ?string
    {
        return $this->storage->get($this->createAuthKeyStorageKey($authKey));
    }

    /**
     * Redisから認証キーを削除する
     *
     * @param string $key
     */
    public function deleteAuthKey(string $key): void
    {
        $this->storage->delete($this->createAuthKeyStorageKey($key));
    }
}
