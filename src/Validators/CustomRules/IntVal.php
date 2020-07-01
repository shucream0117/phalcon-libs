<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

use Phalcon\Validation\Validator\Numericality;

/*
 * 数字であるかどうかを検証する Numericality というバリデータがあるが、
 * HogeType, HogeVal というバリデーターのネーミングに一貫性を持たせるためにIntValクラスで継承している
 */
class IntVal extends Numericality
{
}
