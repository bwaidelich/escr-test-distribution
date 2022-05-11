<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection\ContentGraph;

use Neos\ContentRepository\Projection\ProjectionStateInterface;

final class ContentGraph implements ProjectionStateInterface
{
    public function __construct(
        private readonly ContentGraphRepositoryInterface $repository
    ) {}

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
