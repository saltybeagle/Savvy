<?php

class Savvy_ObjectProxy_ArrayAccess extends Savvy_ObjectProxy implements ArrayAccess
{
    function offsetExists($offset)
    {
        return $this->object->offsetExists();
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