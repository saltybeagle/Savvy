<?php
class Savvy_ObjectProxy_Traversable extends Savvy_ObjectProxy implements IteratorAggregate
{
    function getIterator()
    {
        return $this->object;
    }

    function current()
    {
        return self::filterVar(parent::current());
    }
}