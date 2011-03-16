<?php
class Savvy_ObjectProxy_ArrayIterator extends Savvy_ObjectProxy_ArrayAccess implements Iterator, SeekableIterator, Countable 
{

    /**
     * Construct a new object proxy
     *
     * @param array $array  The array
     * @param Main  $savant The savant templating system
     */
    function __construct($array, $savvy)
    {
        parent::__construct(new ArrayIterator($array), $savvy);
    }

    function current()
    {
        return $this->object->current();
    }

    function next()
    {
        return $this->object->next();
    }

    function key()
    {
        return $this->object->key();
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

}
