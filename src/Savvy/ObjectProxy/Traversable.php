<?php
class Savvy_ObjectProxy_Traversable extends Savvy_ObjectProxy implements OuterIterator
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

    public function rewind():bool
    {
        $this->getInnerIterator()->rewind();
    }

    public function count():int
    {
        if ($this->getInnerIterator() instanceof Countable) {
            return count($this->getInnerIterator());
        }

        return iterator_count($this->getInnerIterator());
    }
}
