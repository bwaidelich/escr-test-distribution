<?php
namespace Neos\ContentRepositoryRegistry\Exception;

use Neos\ContentRepository\ValueObject\ContentRepositoryId;

final class ContentRepositoryNotFound extends \InvalidArgumentException
{

    public static function notConfigured(ContentRepositoryId $contentRepositoryId): self
    {
        return new self(sprintf('A content repository with id "%s" is not configured', $contentRepositoryId->value), 1650557155);
    }
}
