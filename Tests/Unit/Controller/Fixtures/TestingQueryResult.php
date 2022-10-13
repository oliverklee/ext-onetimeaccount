<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
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

    public function current()
    {
        return $this->objectStorage->current();
    }

    public function next()
    {
        $this->objectStorage->next();
    }

    public function key()
    {
        return $this->objectStorage->key();
    }

    public function valid()
    {
        return $this->objectStorage->valid();
    }

    public function rewind()
    {
        $this->objectStorage->rewind();
    }

    public function offsetExists($offset)
    {
        return $this->objectStorage->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->objectStorage->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->objectStorage->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->objectStorage->offsetUnset($offset);
    }

    public function count()
    {
        return $this->objectStorage->count();
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function getQuery()
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    public function getFirst()
    {
        $this->objectStorage->rewind();
        return $this->objectStorage->current();
    }

    public function toArray()
    {
        return $this->objectStorage->toArray();
    }
}
