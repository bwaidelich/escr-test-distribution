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
use Neos\ContentRepository\Projection\ContentStream\ContentStreamRepositoryInterface;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\EventStore\Model\EventStore\SetupResult;
use Neos\EventStore\ProvidesSetupInterface;

final class DoctrineContentStreamRepository implements ContentStreamRepositoryInterface, ProvidesSetupInterface
{

    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
    ) {}


    public function add(ContentStreamId $contentStreamId): void
    {
        $this->connection->insert($this->tableName, ['id' => $contentStreamId->value]);
    }

    public function remove(ContentStreamId $contentStreamId): void
    {
        $this->connection->delete($this->tableName, ['id' => $contentStreamId->value]);
    }

    public function findAll(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM ' . $this->connection->quoteIdentifier($this->tableName));
    }

    public function findOneById(ContentStreamId $contentStreamId): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM ' . $this->connection->quoteIdentifier($this->tableName) . ' WHERE id = :contentStreamId', ['contentStreamId' => $contentStreamId->value]);
        return $row !== false ? $row : null;
    }

    public function setup(): SetupResult
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (!$schemaManager instanceof AbstractSchemaManager) {
            throw new \RuntimeException('Failed to retrieve Schema Manager', 1625653914);
        }
        $schema = new Schema();

        $tableContentStream = $schema->createTable($this->tableName);
        $tableContentStream->addColumn('id', Types::STRING, ['length' => 40]);
        $tableContentStream->setPrimaryKey(['id']);

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
