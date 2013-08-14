<?php
class Savvy_ObjectProxy_ArrayIterator extends Savvy_ObjectProxy implements Iterator, ArrayAccess, SeekableIterator, Countable
{

    /**
     * Construct a new object proxy
     *
     * @param array $array  The array
     * @param Main  $savant The savant templating system
     */
    public function __construct($array, $savvy)
    {
        if (!($array instanceof ArrayIterator)) {
            $array = new ArrayIterator($array);
        }
        parent::__construct($array, $savvy);
    }

    public function current()
    {
        return $this->filterVar($this->object->current());
    }

    public function next()
    {
        return $this->object->next();
    }

    public function key()
    {
        return $this->filterVar($this->object->key());
    }

    public function valid()
    {
        return $this->object->valid();
    }

    public function rewind()
    {
        return $this->object->rewind();
    }

    public function seek($offset)
    {
        return $this->object->seek($offset);
    }

    public function offsetExists($offset)
    {
        return $this->object->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->filterVar($this->object->offsetGet($offset));
    }

    public function offsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->object->offsetUnset($offset);
    }
}
