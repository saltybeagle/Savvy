<?php
class Savvy_ObjectProxy_ArrayAccess extends Savvy_ObjectProxy implements ArrayAccess
{
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
