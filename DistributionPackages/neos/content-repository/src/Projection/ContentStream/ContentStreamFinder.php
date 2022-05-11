<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection\ContentStream;

use Neos\ContentRepository\Projection\ProjectionStateInterface;

final class ContentStreamFinder implements ProjectionStateInterface
{
    public function __construct(
        private readonly ContentStreamRepositoryInterface $repository
    ) {}

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
