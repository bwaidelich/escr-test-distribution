<?php
declare(strict_types=1);
namespace Wwwision\Test\Command;


use Neos\ContentRepository\Command\CreateContentStream;
use Neos\ContentRepository\Command\CreateNode;
use Neos\ContentRepository\Command\RemoveContentStream;
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
     * Setup EventStore and projections for the specified site
     *
     * @param string $site
     */
    public function setupCommand(string $site): void
    {
        $this->crForSite($site)->setUp();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * Reset projection states of the specified site
     *
     * @param string $site
     */
    public function resetCommand(string $site): void
    {
        $this->crForSite($site)->resetProjectionStates();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * Replay (i.e. reset and catch up) projections for the specified site
     *
     * @param string $site
     */
    public function replayCommand(string $site): void
    {
        $this->crForSite($site)->resetProjectionStates();
        $this->catchupCommand($site);
    }

    /**
     * Catch up projections of the specified site
     *
     * @param string $site
     */
    public function catchupCommand(string $site): void
    {
        $this->crForSite($site)->catchUpProjection(ContentStreamProjection::class);
        $this->crForSite($site)->catchUpProjection(ContentGraphProjection::class);
        $this->outputLine('<success>Done</success>');
    }

    // ----

    /**
     * Create a content stream in the specified site
     *
     * @param string $site
     * @param string $contentStream
     */
    public function createContentStreamCommand(string $site, string $contentStream): void
    {
        $this->crForSite($site)->handle(CreateContentStream::with(ContentStreamId::fromString($contentStream)))->block();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * Remove a content stream within the specified site
     *
     * @param string $site
     * @param string $contentStream
     */
    public function removeContentStreamCommand(string $site, string $contentStream): void
    {
        $this->crForSite($site)->handle(RemoveContentStream::with(ContentStreamId::fromString($contentStream)))->block();
        $this->outputLine('<success>Done</success>');
    }

    /**
     * Add a node in a content stream of a site
     *
     * @param string $site
     * @param string $contentStream
     * @param string $nodeId
     */
    public function createNodeCommand(string $site, string $contentStream, string $nodeId): void
    {
        $this->crForSite($site)->handle(CreateNode::with(ContentStreamId::fromString($contentStream), NodeId::fromString($nodeId)))->block();
        $this->outputLine('<success>Done</success>');
    }


    /**
     *
     * List all content streams of the specified site
     *
     * @param string $site
     */
    public function getContentStreamsCommand(string $site): void
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
