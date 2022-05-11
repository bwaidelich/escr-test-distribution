<?php
declare(strict_types=1);

namespace Neos\ContentRepository\Command;

use Neos\ContentRepository\ValueObject\ContentStreamId;

final class CreateContentStream implements CommandInterface
{

    private function __construct(
        public readonly ContentStreamId $contentStreamId,
    ) {}

    public static function for(ContentStreamId $contentStreamId): self
    {
        return new self($contentStreamId);
    }
}