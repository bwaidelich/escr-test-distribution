<?php
declare(strict_types=1);
namespace Neos\ContentRepository\ValueObject;

final class ContentStreamId implements \JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

}
