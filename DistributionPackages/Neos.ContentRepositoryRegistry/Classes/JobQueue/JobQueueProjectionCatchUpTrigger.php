<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry\JobQueue;

use Flowpack\JobQueue\Common\Job\JobManager;
use Neos\ContentRepository\Projection\ProjectionCatchUpTriggerInterface;
use Neos\ContentRepository\Projection\Projections;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;

final class JobQueueProjectionCatchUpTrigger implements ProjectionCatchUpTriggerInterface
{
    public function __construct(
        private readonly ContentRepositoryId $contentRepositoryId,
        private readonly JobManager $jobManager,
    ) {}

    public function triggerCatchUp(Projections $projections): void
    {
        foreach ($projections as $projection) {
            $this->jobManager->queue('Neos.ContentRepository:Projections', new CatchUpProjectionJob($this->contentRepositoryId, $projection));
        }
    }
}
