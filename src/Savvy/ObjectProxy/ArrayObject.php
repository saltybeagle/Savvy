<?php
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
class Savvy_ObjectProxy_ArrayObject extends Savvy_ObjectProxy_TraversableArrayAccess
{
    public function __construct($object, $savvy)
    {
        if (!$object instanceof ArrayObject) {
            throw new Savvy_UnexpectedValueException('$object must be an ArrayObject');
        }

        parent::__construct($object, $savvy);
    }
}
