<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Firebase;

class Payload
{
    private string $title;
    private string $body;
    private ?string $route;
    private ?string $link;

    public function __construct(string $title, string $body, ?string $route, ?string $link)
    {
        $this->title = $title;
        $this->body = $body;
        $this->route = $route;
        $this->link = $link;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'route' => $this->route,
            'link' => $this->link,
        ];
    }
}
