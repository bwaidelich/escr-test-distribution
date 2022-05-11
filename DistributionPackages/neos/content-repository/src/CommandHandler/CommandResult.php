<?php
declare(strict_types=1);
namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\EventStore\Model\CommitResult;
use Neos\EventStore\Model\SequenceNumber;

final class CommandResult
{
    public function __construct(
        private readonly PendingProjections $pendingProjections,
        public readonly CommitResult $commitResult,
    )
    {}

    public function block(): void
    {
        foreach ($this->pendingProjections as $pendingProjection) {
            $expectedSequenceNumber = $this->pendingProjections->getExpectedSequenceNumber($pendingProjection);
            $this->blockProjection($pendingProjection, $expectedSequenceNumber);
        }
    }

    private function blockProjection(ProjectionInterface $projection, SequenceNumber $expectedSequenceNumber): void
    {
        $attempts = 0;
        while ($projection->getSequenceNumber()->value < $expectedSequenceNumber->value) {
            usleep(50000); // 50000μs = 50ms
            if (++$attempts > 100) { // 5 seconds
                throw new \RuntimeException(sprintf('TIMEOUT while waiting for projection "%s" to catch up to sequence number %d - check the error logs for details.', $projection::class, $expectedSequenceNumber->value), 1550232279);
            }
        }
    }
}
