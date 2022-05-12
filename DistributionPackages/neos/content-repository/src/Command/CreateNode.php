<?php
declare(strict_types=1);

namespace Neos\ContentRepository\Command;

use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;

final class CreateNode implements CommandInterface
{

    private function __construct(
        public readonly ContentStreamId $contentStreamId,
        public readonly NodeId $nodeId,
    ) {}

    public static function with(ContentStreamId $contentStreamId, NodeId $nodeId): self
    {
        return new self($contentStreamId, $nodeId);
    }
}
