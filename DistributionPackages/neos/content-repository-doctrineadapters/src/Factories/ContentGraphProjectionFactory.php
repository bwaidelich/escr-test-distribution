<?php
declare(strict_types=1);
namespace Neos\ContentRepository\DoctrineAdapters\Factories;

use Doctrine\DBAL\Connection;
use Neos\ContentRepository\DoctrineAdapters\DoctrineContentGraphRepository;
use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\ContentRepository\Projection\ContentGraph\ContentGraphProjection;
use Neos\ContentRepository\Projection\ProjectionFactoryInterface;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\EventStore\DoctrineAdapter\DoctrineCheckpointStorage;

final class ContentGraphProjectionFactory implements ProjectionFactoryInterface
{
    public function __construct(
        private Connection $connection,
        private EventNormalizer $eventNormalizer,
    ) {}

    /**
     * @param ContentRepositoryId $contentRepositoryId
     * @param array<mixed> $options
     * @return ContentGraphProjection
     **/
    public function create(ContentRepositoryId $contentRepositoryId, array $options): ContentGraphProjection
    {
        $repository = new DoctrineContentGraphRepository($this->connection, $contentRepositoryId->value . '_contentgraph');
        $checkpointStorage = new DoctrineCheckpointStorage($this->connection, $contentRepositoryId->value . '_checkpoints', ContentGraphProjection::class);
        return new ContentGraphProjection($repository, $this->eventNormalizer, $checkpointStorage);
    }
}
