<?php
declare(strict_types=1);

namespace Neos\ContentRepository\Event;

use Neos\ContentRepository\EventStore\EventInterface;
use Neos\ContentRepository\ValueObject\ContentStreamId;

final class ContentStreamWasRemoved implements EventInterface
{

    public function __construct(
        public readonly ContentStreamId $contentStreamId,
    ) {}

    /**
     * @param array<mixed> $values
     */
    public static function fromArray(array $values): self
    {
        assert(isset($values['contentStreamId']) && is_string($values['contentStreamId']));
        return new self(
            ContentStreamId::fromString($values['contentStreamId']),
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
