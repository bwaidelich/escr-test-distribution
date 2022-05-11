<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry\Factories;

use Flowpack\JobQueue\Common\Job\JobManager;
use Neos\ContentRepository\Projection\ProjectionCatchUpTriggerFactoryInterface;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\JobQueue\JobQueueProjectionCatchUpTrigger;
use Neos\Flow\Annotations as Flow;

#[Flow\Scope("singleton")]
final class JobQueueProjectionCatchUpTriggerFactory implements ProjectionCatchUpTriggerFactoryInterface
{
    public function __construct(
        private readonly JobManager $jobManager,
    ) {}

    public function create(ContentRepositoryId $contentRepositoryId, array $options): JobQueueProjectionCatchUpTrigger
    {
        return new JobQueueProjectionCatchUpTrigger($contentRepositoryId, $this->jobManager);
    }
}
