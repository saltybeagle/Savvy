<?php
class Savvy_ObjectProxy_Traversable extends Savvy_ObjectProxy implements IteratorAggregate
{
    function getIterator()
    {
        return $this->object;
    }
}