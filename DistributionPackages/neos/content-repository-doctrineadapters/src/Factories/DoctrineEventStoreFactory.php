<?php
declare(strict_types=1);
namespace Neos\ContentRepository\DoctrineAdapters\Factories;

use Doctrine\DBAL\Connection;
use Neos\ContentRepository\EventStore\EventStoreFactoryInterface;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\EventStore\DoctrineAdapter\DoctrineEventStore;
use Neos\EventStore\EventStoreInterface;

final class DoctrineEventStoreFactory implements EventStoreFactoryInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    public function create(ContentRepositoryId $contentRepositoryId, array $options): EventStoreInterface
    {
        return new DoctrineEventStore($this->connection, $contentRepositoryId->value . '_events');
    }
}
