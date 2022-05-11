<?php
declare(strict_types=1);

namespace Neos\ContentRepository\Event;

use Neos\ContentRepository\EventStore\EventInterface;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;

final class NodeWasCreated implements EventInterface
{

    public function __construct(
        public readonly ContentStreamId $contentStreamId,
        public readonly NodeId $nodeId,
    ) {}

    /**
     * @param array<mixed> $values
     */
    public static function fromArray(array $values): self
    {
        assert(isset($values['contentStreamId']) && is_string($values['contentStreamId']));
        assert(isset($values['nodeId']) && is_string($values['nodeId']));
        return new self(
            ContentStreamId::fromString($values['contentStreamId']),
            NodeId::fromString($values['nodeId']),
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
