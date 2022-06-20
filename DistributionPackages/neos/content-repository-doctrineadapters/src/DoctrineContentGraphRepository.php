<?php
declare(strict_types=1);
namespace Neos\ContentRepository\DoctrineAdapters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Neos\ContentRepository\Projection\ContentGraph\ContentGraphRepositoryInterface;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;
use Neos\EventStore\Model\EventStore\SetupResult;
use Neos\EventStore\ProvidesSetupInterface;

final class DoctrineContentGraphRepository implements ContentGraphRepositoryInterface, ProvidesSetupInterface
{

    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
    ) {}


    public function add(ContentStreamId $contentStreamId, NodeId $nodeId): void
    {
        $this->connection->insert($this->tableName, ['contentstreamid' => $contentStreamId->value, 'nodeid' => $nodeId->value]);
    }

    public function remove(ContentStreamId $contentStreamId, NodeId $nodeId): void
    {
        $this->connection->delete($this->tableName, ['contentstreamid' => $contentStreamId->value, 'nodeid' => $nodeId->value]);
    }

    public function findAll(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM ' . $this->connection->quoteIdentifier($this->tableName));
    }

    public function setup(): SetupResult
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (!$schemaManager instanceof AbstractSchemaManager) {
            throw new \RuntimeException('Failed to retrieve Schema Manager', 1625653914);
        }
        $schema = new Schema();

        $tableContentStream = $schema->createTable($this->tableName);
        $tableContentStream->addColumn('contentstreamid', Types::STRING, ['length' => 40]);
        $tableContentStream->addColumn('nodeid', Types::STRING, ['length' => 40]);

        $schemaDiff = (new Comparator())->compare($schemaManager->createSchema(), $schema);
        foreach ($schemaDiff->toSaveSql($this->connection->getDatabasePlatform()) as $statement) {
            $this->connection->executeStatement($statement);
        }
        return SetupResult::success('');
    }

    public function reset(): void
    {
        try {
            $databasePlatform = $this->connection->getDatabasePlatform();
            if (!$databasePlatform instanceof AbstractPlatform) {
                throw new \RuntimeException(sprintf('Expected instance of %s, got: %s', AbstractPlatform::class, get_debug_type($databasePlatform)), 1650119509);
            }
        } catch (DbalException | \RuntimeException $e) {
            throw new \RuntimeException(sprintf('Failed to retrieve database platform: %s', $e->getMessage()), 1650119312, $e);
        }
        try {
            $this->connection->executeStatement($databasePlatform->getTruncateTableSQL($this->tableName));
        } catch (DbalException $e) {
            throw new \RuntimeException(sprintf('Failed to truncate database table "%s": %s', $this->tableName, $e->getMessage()), 1650119464, $e);
        }
    }
}
