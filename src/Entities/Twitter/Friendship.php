<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Twitter;

class Friendship
{
    const FOLLOWING = 'following';
    const FOLLOWING_REQUESTED = 'following_requested';
    const FOLLOWED_BY = 'followed_by';
    const NONE = 'none';
    const BLOCKING = 'blocking';
    const MUTING = 'muting';

    private string $id;
    private string $screenName;
    private string $name;
    /** @var string[] */
    private array $friendshipStatuses;

    /**
     * @param string $id
     * @param string $screenName
     * @param string $name
     * @param string[] $friendshipStatuses
     */
    public function __construct(
        string $id,
        string $screenName,
        string $name,
        array $friendshipStatuses = []
    ) {
        $this->id = $id;
        $this->screenName = $screenName;
        $this->name = $name;
        $this->friendshipStatuses = $friendshipStatuses;
    }

    public static function fromJson(array $data): self
    {
        return new static(
            $data['id_str'],
            $data['screen_name'],
            $data['name'],
            $data['connections']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getScreenName(): string
    {
        return $this->screenName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFriendshipStatuses(): array
    {
        return $this->friendshipStatuses;
    }

    public function isFollowing(): bool
    {
        return in_array(self::FOLLOWING, $this->getFriendshipStatuses());
    }

    public function isFollowed(): bool
    {
        return in_array(self::FOLLOWED_BY, $this->getFriendshipStatuses());
    }

    public function isFollowRequestSent(): bool
    {
        return in_array(self::FOLLOWING_REQUESTED, $this->getFriendshipStatuses());
    }

    public function isNone(): bool
    {
        return in_array(self::NONE, $this->getFriendshipStatuses());
    }

    public function isBlocking(): bool
    {
        return in_array(self::BLOCKING, $this->getFriendshipStatuses());
    }

    public function isMuting(): bool
    {
        return in_array(self::MUTING, $this->getFriendshipStatuses());
    }
}
