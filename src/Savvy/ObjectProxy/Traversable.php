<?php
class Savvy_ObjectProxy_Traversable extends Savvy_ObjectProxy implements Iterator
{

    public function getIterator()
    {
        return $this->object;
    }

    public function next()
    {
        $this->object->next();
    }

    public function key()
    {
        return $this->object->key();
    }

    public function valid()
    {
        return $this->object->valid();
    }

    public function rewind()
    {
        $this->object->rewind();
    }

    public function current()
    {
        return $this->filterVar($this->object->current());
    }
}
