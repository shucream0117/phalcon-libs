<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models;

interface Loginable
{
    /**
     * ログイン主体の識別子(通常はid)を返す
     * @return string
     */
    public function getIdentifier(): string;
}
