<?php
declare(strict_types=1);
namespace Neos\ContentRepository\DoctrineAdapters\Factories;

use Doctrine\DBAL\Connection;
use Neos\ContentRepository\DoctrineAdapters\DoctrineContentStreamRepository;
use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamProjection;
use Neos\ContentRepository\Projection\ProjectionFactoryInterface;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\EventStore\DoctrineAdapter\DoctrineCheckpointStorage;

final class ContentStreamProjectionFactory implements ProjectionFactoryInterface
{
    public function __construct(
        private Connection $connection,
        private EventNormalizer $eventNormalizer,
    ) {}

    /**
     * @param ContentRepositoryId $contentRepositoryId
     * @param array<mixed> $options
     * @return ContentStreamProjection
     **/
    public function create(ContentRepositoryId $contentRepositoryId, array $options): ContentStreamProjection
    {
        $repository = new DoctrineContentStreamRepository($this->connection, $contentRepositoryId->value . '_contentstream');
        $checkpointStorage = new DoctrineCheckpointStorage($this->connection, $contentRepositoryId->value . '_checkpoints', ContentStreamProjection::class);
        return new ContentStreamProjection($repository, $this->eventNormalizer, $checkpointStorage);
    }
}
