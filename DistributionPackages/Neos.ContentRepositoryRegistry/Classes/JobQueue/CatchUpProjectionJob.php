<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry\JobQueue;

use Flowpack\JobQueue\Common\Job\JobInterface;
use Flowpack\JobQueue\Common\Queue\Message;
use Flowpack\JobQueue\Common\Queue\QueueInterface;
use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\ContentRepository\Projection\ProjectionStateInterface;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;

final class CatchUpProjectionJob implements JobInterface
{

    /**
     * @var ContentRepositoryRegistry
     */
    #[Flow\Inject(lazy: false)]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    protected string $contentRepositoryId;
    /**
     * @var class-string<ProjectionInterface<ProjectionStateInterface>>
     */
    protected string $projectionClassName;

    /**
     * @param ContentRepositoryId $contentRepositoryId
     * @param ProjectionInterface<ProjectionStateInterface> $projection
     */
    public function __construct(
        ContentRepositoryId $contentRepositoryId,
        ProjectionInterface $projection
    ) {
        $this->contentRepositoryId = $contentRepositoryId->value;
        $this->projectionClassName = $projection::class;
    }

    public function execute(QueueInterface $queue, Message $message): bool
    {
        $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString($this->contentRepositoryId));
        $contentRepository->catchUpProjection($this->projectionClassName);
        return true;
    }

    public function getLabel(): string
    {
        return sprintf('Catch up projection "%s" on content repository "%s"', $this->projectionClassName, $this->contentRepositoryId);
    }
}
