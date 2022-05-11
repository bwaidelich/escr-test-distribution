<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection\ContentGraph;

use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;

interface ContentGraphRepositoryInterface
{

    public function add(ContentStreamId $contentStreamId, NodeId $nodeId): void;
    public function remove(ContentStreamId $contentStreamId, NodeId $nodeId): void;

    public function findAll(): array;

    public function reset(): void;

}
