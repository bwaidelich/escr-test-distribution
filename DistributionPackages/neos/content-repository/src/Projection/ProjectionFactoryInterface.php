<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection;

use Neos\ContentRepository\ValueObject\ContentRepositoryId;

interface ProjectionFactoryInterface
{
    /**
     * @param ContentRepositoryId $contentRepositoryId
     * @param array<mixed> $options
     * @return ProjectionInterface<ProjectionStateInterface>
     */
    public function create(ContentRepositoryId $contentRepositoryId, array $options): ProjectionInterface;
}
