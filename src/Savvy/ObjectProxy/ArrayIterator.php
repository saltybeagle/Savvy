<?php
class Savvy_ObjectProxy_ArrayIterator extends Savvy_ObjectProxy_TraversableArrayAccess implements SeekableIterator
{
    /**
     * Construct a new object proxy
     *
     * @param array $array  The array
     * @param Main  $savant The savant templating system
     */
    public function __construct($array, $savvy)
    {
        if (!$array instanceof ArrayIterator) {
            $array = new ArrayIterator($array);
        }
        parent::__construct($array, $savvy);
    }

    public function seek($offset)
    {
        return $this->getInnerIterator()->seek($offset);
    }
}
