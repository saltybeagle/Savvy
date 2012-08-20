<?php
class Savvy_ObjectProxy_ArrayIterator extends Savvy_ObjectProxy implements Iterator, ArrayAccess, SeekableIterator, Countable
{

    /**
     * Construct a new object proxy
     *
     * @param array $array  The array
     * @param Main  $savant The savant templating system
     */
    function __construct($array, $savvy)
    {
        if (!($array instanceof ArrayIterator)) {
            $array = new ArrayIterator($array);
        }
        parent::__construct($array, $savvy);
    }

    function current()
    {
        return $this->filterVar($this->object->current());
    }

    function next()
    {
        return $this->object->next();
    }

    function key()
    {
        return $this->filterVar($this->object->key());
    }

    function valid()
    {
        return $this->object->valid();
    }

    function rewind()
    {
        return $this->object->rewind();
    }

    function seek($offset)
    {
        return $this->object->seek($offset);
    }

    function offsetExists($offset)
    {
        return $this->object->offsetExists($offset);
    }

    function offsetGet($offset)
    {
        return $this->filterVar($this->object->offsetGet($offset));
    }

    function offsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
    }

    function offsetUnset($offset)
    {
        $this->object->offsetUnset($offset);
    }
}
