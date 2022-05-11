<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry;

use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\Exception\ContentRepositoryNotFound;
use Neos\ContentRepositoryRegistry\Exception\InvalidConfigurationException;
use Neos\ContentRepositoryRegistry\Factories\ContentRepositoryFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\Arrays;

#[Flow\Scope("singleton")]
final class ContentRepositoryRegistry
{
    /**
     * @var array<string, ContentRepository>
     */
    private static array $instances = [];

    /**
     * @param array<mixed> $settings
     * @param ContentRepositoryFactory $factory
     */
    public function __construct(
        private readonly array $settings,
        private readonly ContentRepositoryFactory $factory,
    ) {}

    /**
     * @throws ContentRepositoryNotFound | InvalidConfigurationException
     */
    public function get(ContentRepositoryId $contentRepositoryId): ContentRepository
    {
        if (!array_key_exists($contentRepositoryId->value, self::$instances)) {
            self::$instances[$contentRepositoryId->value] = $this->buildInstance($contentRepositoryId);
        }
        return self::$instances[$contentRepositoryId->value];
    }

    /**
     * @throws ContentRepositoryNotFound | InvalidConfigurationException
     */
    private function buildInstance(ContentRepositoryId $contentRepositoryId): ContentRepository
    {
        assert(is_array($this->settings['contentRepositories']));
        assert(isset($this->settings['contentRepositories'][$contentRepositoryId->value]) && is_array($this->settings['contentRepositories'][$contentRepositoryId->value]), ContentRepositoryNotFound::notConfigured($contentRepositoryId));
        $contentRepositorySettings = $this->settings['contentRepositories'][$contentRepositoryId->value];
        if (isset($contentRepositorySettings['preset'])) {
            assert(isset($this->settings['presets']) && is_array($this->settings['presets']), InvalidConfigurationException::fromMessage('Content repository settings "%s" refer to a preset "%s", but there are not presets configured', $contentRepositoryId->value, $contentRepositorySettings['preset']));
            assert(isset($this->settings['presets'][$contentRepositorySettings['preset']]) && is_array($this->settings['presets'][$contentRepositorySettings['preset']]), InvalidConfigurationException::missingPreset($contentRepositoryId, $contentRepositorySettings['preset']));
            $contentRepositorySettings = Arrays::arrayMergeRecursiveOverrule($contentRepositorySettings, $this->settings['presets'][$contentRepositorySettings['preset']]);
        }
        array_walk_recursive($contentRepositorySettings, static fn(&$value) => $value = is_string($value) ? str_replace('{contentRepositoryId}', $contentRepositoryId->value, $value) : $value);
        try {
            return $this->factory->create($contentRepositoryId, $contentRepositorySettings);
        } catch (\Exception $exception) {
            throw InvalidConfigurationException::fromException($contentRepositoryId, $exception);
        }
    }

}
