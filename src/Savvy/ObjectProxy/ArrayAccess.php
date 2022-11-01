<?php
class Savvy_ObjectProxy_ArrayAccess extends Savvy_ObjectProxy implements ArrayAccess
{
    public function offsetExists($offset):bool
    {
        return $this->object->offsetExists($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->filterVar($this->object->offsetGet($offset));
    }

    public function offsetSet($offset, $value):void
    {
        $this->object->offsetSet($offset, $value);
    }

    public function offsetUnset($offset):void
    {
        $this->object->offsetUnset($offset);
    }
}
