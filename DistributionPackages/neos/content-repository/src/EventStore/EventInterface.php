<?php
declare(strict_types=1);

namespace Neos\ContentRepository\EventStore;

interface EventInterface extends \JsonSerializable
{
    public static function fromArray(array $values): self;
}
