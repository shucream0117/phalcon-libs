<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models\Behaviors;

use Ulid\Ulid;

trait UlidTrait
{
    /**
     * フレームワークとの相性の問題であえてtyped parameterにしないでおく。
     * 初期化前にアクセスされるとエラーになるため。
     *
     * @var string
     */
    public $id;
    public static string $COLUMN_ID = 'id';

    public function getId(): string
    {
        return $this->id;
    }

    public function getIdAsUlid(): Ulid
    {
        return Ulid::fromString($this->getId());
    }
}
