<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

final class CommitResult
{
    public function __construct(
        // TODO rename highestCommittedVersion
        public readonly Version $version,
        // TODO rename highestCommittedSequenceNumber
        public readonly SequenceNumber $sequenceNumber,
    ) {}
}
