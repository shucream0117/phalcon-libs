<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Closure;
use Phalcon\Db\Adapter\AdapterInterface;
use Throwable;

/*
 * Phalconの Transaction\Manager クラスもあるが、
 * トランザクションを利用するメソッドをネストして呼び出す時がややこしいので、
 * execInTx() にクロージャを渡すことで必要な時だけ新規Txを生成して実行するようにし、
 * 既にトランザクションが開始されている場合は何もせずそのまま実行するようにするためのクラスです
 */
class DBTransaction
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param Closure $closure
     * @return mixed
     * @throws Throwable
     */
    public function execInTx(Closure $closure)
    {
        if ($this->adapter->isUnderTransaction()) { // 既に外側でトランザクション開始している場合は何もしない
            return $closure();
        }
        $this->adapter->begin();
        try {
            $result = $closure();
            $this->adapter->commit();
            return $result;
        } catch (Throwable $e) {
            $this->adapter->rollback();
            throw $e;
        }
    }
}
