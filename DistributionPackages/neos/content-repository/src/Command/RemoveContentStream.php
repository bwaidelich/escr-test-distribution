<?php
declare(strict_types=1);

namespace Neos\ContentRepository\Command;

use Neos\ContentRepository\ValueObject\ContentStreamId;

final class RemoveContentStream implements CommandInterface
{

    private function __construct(
        public readonly ContentStreamId $contentStreamId,
    ) {}

    public static function with(ContentStreamId $contentStreamId): self
    {
        return new self($contentStreamId);
    }
}
