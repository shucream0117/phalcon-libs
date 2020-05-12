<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Twitter;

abstract class AbstractUser
{
    protected string $id;

    public function getId(): string
    {
        return $this->id;
    }
}
