<?php

/**
 * Savvy_ObjectProxy_ArrayObject
 *
 * PHP version 5
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 Brett Bieber, 2011 Michael Gauthier
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/saltybeagle/Savvy
 */

/**
 * Proxies ArrayObject objects
 *
 * Filters on array access or on traversal.
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 Brett Bieber, 2011 Michael Gauthier
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/saltybeagle/Savvy
 */
class Savvy_ObjectProxy_ArrayObject
    extends Savvy_ObjectProxy_ArrayAccess
    implements ArrayAccess, Countable, Serializable, IteratorAggregate
{
    public function getIterator()
    {
        return $this->object->getIterator();
    }

    public function next()
    {
        $this->object->next();
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
        $this->object->rewind();
    }

    public function current()
    {
        return $this->filterVar($this->object->current());
    }

    public function count()
    {
        return count($this->object);
    }

    public function serialize()
    {
        return serialize($this->object);
    }

    public function unserialize($string)
    {
        $object = unserialize($string);
        if ($object !== false) {
            $this->object = $object;
        }
    }
}
