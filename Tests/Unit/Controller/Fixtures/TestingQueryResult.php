<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Testing query result that holds an object storage for its objects.
 *
 * @implements QueryResultInterface<FrontendUserGroup>
 */
final class TestingQueryResult implements QueryResultInterface
{
    /**
     * @var ObjectStorage<FrontendUserGroup>
     */
    private $objectStorage;

    /**
     * @param ObjectStorage<FrontendUserGroup> $storage
     */
    public function __construct(ObjectStorage $storage)
    {
        $this->objectStorage = $storage;
    }

    public function current(): FrontendUserGroup
    {
        return $this->objectStorage->current();
    }

    public function next(): void
    {
        $this->objectStorage->next();
    }

    public function key(): string
    {
        return $this->objectStorage->key();
    }

    public function valid(): bool
    {
        return $this->objectStorage->valid();
    }

    public function rewind(): void
    {
        $this->objectStorage->rewind();
    }

    public function offsetExists($offset): bool
    {
        return $this->objectStorage->offsetExists($offset);
    }

    public function offsetGet($offset): ?FrontendUserGroup
    {
        return $this->objectStorage->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->objectStorage->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->objectStorage->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->objectStorage->count();
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function getQuery(): QueryInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    public function getFirst(): ?FrontendUserGroup
    {
        $this->objectStorage->rewind();
        return $this->objectStorage->current();
    }

    /**
     * @return array<int, FrontendUserGroup>
     */
    public function toArray(): array
    {
        return $this->objectStorage->toArray();
    }
}
