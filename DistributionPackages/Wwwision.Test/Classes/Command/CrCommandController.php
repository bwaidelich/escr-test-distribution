<?php
declare(strict_types=1);
namespace Wwwision\Test\Command;


use Neos\ContentRepository\Command\CreateContentStream;
use Neos\ContentRepository\Command\CreateNode;
use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\Projection\ContentGraph\ContentGraphProjection;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamProjection;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Cli\CommandController;

class CrCommandController extends CommandController
{

    public function __construct(
        private ContentRepositoryRegistry $registry,
    )
    {
        parent::__construct();
    }

    /**
     * @param string $site
     */
    public function setupCommand(string $site): void
    {
        $this->crForSite($site)->setUp();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * @param string $site
     */
    public function resetCommand(string $site): void
    {
        $this->crForSite($site)->resetProjectionStates();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * @param string $site
     */
    public function replayCommand(string $site): void
    {
        $this->crForSite($site)->resetProjectionStates();
        $this->catchupCommand($site);
    }

    /**
     * @param string $site
     */
    public function catchupCommand(string $site): void
    {
        $this->crForSite($site)->catchUpProjection(ContentStreamProjection::class);
        $this->crForSite($site)->catchUpProjection(ContentGraphProjection::class);
        $this->outputLine('<success>Done</success>');
    }

    /**
     * @param string $site
     * @param string $contentStream
     */
    public function createContentStreamCommand(string $site, string $contentStream): void
    {
        $this->crForSite($site)->handle(CreateContentStream::for(ContentStreamId::fromString($contentStream)))->block();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * @param string $site
     * @param string $contentStream
     * @param string $nodeId
     */
    public function createNodeCommand(string $site, string $contentStream, string $nodeId): void
    {
        $this->crForSite($site)->handle(CreateNode::for(ContentStreamId::fromString($contentStream), NodeId::fromString($nodeId)))->block();
        $this->outputLine('<success>Done</success>');
    }


    /**
     * @param string $site
     */
    public function readCommand(string $site): void
    {
        $state = $this->crForSite($site)->projectionState(ContentStreamProjection::class);
        foreach ($state->findAll() as $row) {
            $this->outputLine(json_encode($row, JSON_THROW_ON_ERROR));
        }
    }

    // -----------------------

    private function crForSite(string $site): ContentRepository
    {
        return $this->registry->get(ContentRepositoryId::fromString($site));
    }
}
