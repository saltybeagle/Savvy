<?php
/**
 * Abstract Savvy_Filter class.
 *
 * You have to extend this class for it to be useful; e.g., "class
 * Savvy_Filter_example extends Savvy_Filter".
 *
 * @package Savvy
 * @author Paul M. Jones <pmjones@ciaweb.net>
 */
abstract class Savvy_FilterAbstract
{

    /**
     * Optional reference to the calling Savvy object.
     *

     * @var Savvy
     */
    protected $savvy = null;

    /**
     * Constructor.
     *

     * @param array $conf An array of configuration keys and values for
     * this filter.
     * @return void
     */
    public function __construct($conf = null)
    {
        settype($conf, 'array');
        foreach ($conf as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Stub method for extended behaviors.
     *

     * @param string $text The text buffer to filter.
     * @return string The text buffer after it has been filtered.
     */
    public static function filter($text)
    {
        return $text;
    }
}
