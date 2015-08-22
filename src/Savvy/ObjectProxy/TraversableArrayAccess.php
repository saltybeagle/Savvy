<?php
class Savvy_ObjectProxy_TraversableArrayAccess extends Savvy_ObjectProxy_ArrayAccess implements OuterIterator
{
    protected $innerIterator;

    public function __construct($object, $savvy)
    {
        if (!$object instanceof Traversable) {
            throw UnexpectedValueException('$object must be traversable');
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
    public function getInnerIterator()
    {
        if ($this->innerIterator) {
            return $this->innerIterator;
        }

        return $this->object;
    }

    public function current()
    {
        return $this->filterVar($this->getInnerIterator()->current());
    }

    public function next()
    {
        $this->getInnerIterator()->next();
    }

    public function key()
    {
        return $this->filterVar($this->getInnerIterator()->key());
    }

    public function valid()
    {
        return $this->getInnerIterator()->valid();
    }

    public function rewind()
    {
        $this->getInnerIterator()->rewind();
    }
}
