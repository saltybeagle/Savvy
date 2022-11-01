<?php
class Savvy_ObjectProxy_TraversableArrayAccess extends Savvy_ObjectProxy_ArrayAccess implements OuterIterator
{
    protected $innerIterator;

    public function __construct($object, $savvy)
    {
        if (!$object instanceof Traversable) {
            throw new Savvy_UnexpectedValueException('$object must be traversable');
        }

        parent::__construct($object, $savvy);

        if ($object instanceof IteratorAggregate) {
            $this->innerIterator = $object->getIterator();
        }
    }

    /**
     * @inheritDoc
     * @return Iterator
     */
    public function getInnerIterator():?Iterator
    {
        if ($this->innerIterator) {
            return $this->innerIterator;
        }

        return $this->object;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->filterVar($this->getInnerIterator()->current());
    }

    public function next():void
    {
        $this->getInnerIterator()->next();
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->filterVar($this->getInnerIterator()->key());
    }

    public function valid():bool
    {
        return $this->getInnerIterator()->valid();
    }

    public function rewind():void
    {
        $this->getInnerIterator()->rewind();
    }

    public function count()
    {
        if ($this->getInnerIterator() instanceof Countable) {
            return count($this->getInnerIterator());
        }

        return iterator_count($this->getInnerIterator());
    }
}
