--TEST--
Escaping ArrayAccess
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);

class ArrayAccessObject extends ArrayObject
{
    function __toString()
    {
        return '<span></span>';
    }
}

class FooBar implements ArrayAccess {
    protected $container = array();

    public function __construct() {
        $this->container = array(
            "one"   => 1,
            "two"   => 2,
            "three" => 3,
        );
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}

class BarBaz extends FooBar implements IteratorAggregate {
    public function getIterator() {
        return new ArrayIterator($this->container);
    }
}

$array = array();
$array[0] = '<h1></h1>';
$array[1] = '<p></p>';

$combined_raw_string = implode($array);
$array = new ArrayAccessObject($array);

$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');
$savvy->setIterateTraversable(false);
$test->assertEquals(false, $savvy->getIterateTraversable(), 'savvy setter fpr iterate traversable should return in getter');
$test->assertEquals(htmlspecialchars((string)$array), $savvy->render($array), 'render ArrayAccess without iterating through template');
$test->assertEquals(htmlspecialchars((string)$array), $savvy->render($array, 'ArrayAccessObject.tpl.php'), 'render ArrayAccess through template without iterating');

// escaped ArrayObject should remain iterateable
$proxiedArray = $savvy->filterVar($array);
foreach ($savvy->filterVar($array) as $proxiedKey => $proxiedValue) {
	echo $proxiedValue;
}
echo "\n";

$proxiedArray[] = 'foo';
if (isset($proxiedArray[2])) {
	unset($proxiedArray[2]);
}

$foobar = new FooBar();
$proxiedObject = $savvy->filterVar($foobar);
$test->assertEquals('1', $proxiedObject['one'], 'proxied array access without iteration');

$barbaz = new BarBaz();
$proxiedObject = $savvy->filterVar($barbaz);
$proxiedObject->getInnerIterator()->seek(1);
$test->assertEquals('2', $proxiedObject->current(), 'proxied array access iterator aggregate without iteration');
$test->assertEquals(3, $proxiedObject->count(), 'proxied array access iterator aggregate without iteration');

try {
	new Savvy_ObjectProxy_TraversableArrayAccess(null, $savvy);
} catch (UnexpectedValueException $e) {}
?>
===DONE===
--EXPECT--
&lt;h1&gt;&lt;/h1&gt;&lt;p&gt;&lt;/p&gt;
===DONE===