<?php

namespace Tests\Unit\Utils;

use Shucream0117\PhalconLib\Utils\DbReconnectorTrait;
use Tests\Unit\TestBase;

class DbReconnectorTraitTest extends TestBase
{
    private function getDummyObject(): object
    {
        return new class() {
            use DbReconnectorTrait;
        };
    }

    /**
     * @covers DbReconnectorTrait::isMysqlGoneAway
     */
    public function testIsMysqlGoneAway()
    {
        $class = $this->getDummyObject();
        $method = new \ReflectionMethod($class, 'isMysqlGoneAway');
        $method->setAccessible(true);

        // getMessage() がfinalなのでモックで置き換えができないため、普通にnewしてしまう
        $pdoException = new \PDOException('SQLSTATE[HY000]: General error: 2006 MySQL server has gone away');
        $this->assertTrue($method->invoke($class, $pdoException));

        $pdoException = new \PDOException('SQLSTATE[HY000]: gone hoge away');
        $this->assertFalse($method->invoke($class, $pdoException));
    }
}
