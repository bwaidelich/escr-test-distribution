<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection\ContentStream;

use Neos\ContentRepository\ValueObject\ContentStreamId;

interface ContentStreamRepositoryInterface
{

    public function add(ContentStreamId $contentStreamId): void;
    public function remove(ContentStreamId $contentStreamId): void;

    public function findAll(): array;

    public function reset(): void;

    public function findOneById(ContentStreamId $contentStreamId): ?array;

}
