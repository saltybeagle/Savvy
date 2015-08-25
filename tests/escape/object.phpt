--TEST--
Savvy::addEscape() object variable escaping test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy(array('escape' => 'htmlspecialchars'));

class Foo
{
    public $var1;

    public function hello()
    {
        return 'World!';
    }
}

$object = new Foo();
$object->var1  = '<p></p>';

$test->assertTrue($savvy->getRawObject($object) == $object, 'savvy raw object passthru');

$proxiedObject = $savvy->filterVar($object);

$test->assertNull($savvy->filterVar(null), 'savvy will passthrough null');

$test->assertEquals(htmlspecialchars($object->var1), $savvy->render($object), 'render object with variable escaping');
$test->assertEquals($object->var1, $savvy->render($object, 'raw.tpl.php'), 'render object with raw variable access');
$test->assertTrue($proxiedObject->getRawObject() == $object, 'access to raw object is same');
$test->assertTrue($savvy->getRawObject($proxiedObject) == $object, 'access to raw object through savvy is same');
$test->assertTrue($savvy->filterVar($proxiedObject) == $proxiedObject, 'proxy is already proxied');
$test->assertTrue(isset($proxiedObject->var1), 'isset works throught proxy');
$proxiedObject->var2 = 'bar';
$test->assertTrue(isset($object->var2), 'setting from proxy sets on object');
unset($proxiedObject->var2);
$test->assertFalse(isset($object->var2), 'unsetting from proxy unsets on object');
$test->assertEquals('World!', $proxiedObject->hello(), 'call proxy object method');
$test->assertEquals(1, $proxiedObject->count(), 'call proxy object count');

$savvy->setEscape();
$test->assertEquals($object->var1, $savvy->render($proxiedObject), 'rendering a proxy when no escape is set renders the object directly');

function ignoreErrorHandler()
{
	return true;
}
set_error_handler('ignoreErrorHandler', E_USER_ERROR);
echo $proxiedObject;
restore_error_handler();

$proxiedObject = $savvy->filterVar(new EmptyIterator());
$test->assertTrue($proxiedObject instanceof Savvy_ObjectProxy_Traversable, 'traverssable proxy');
$test->assertEquals(0, $proxiedObject->count(), 'empty proxied iterator');

class MyData implements IteratorAggregate {
    public $property1 = "Public property one";
    public $property2 = "Public property two";
    public $property3 = "Public property three";

    public function __construct() {
        $this->property4 = "last property";
    }

    public function getIterator() {
        return new ArrayIterator($this);
    }
}

$proxiedObject = $savvy->filterVar(new MyData());
foreach ($proxiedObject as $proxiedKey => $proxiedValue) {
	$proxiedValue = '';
}
$test->assertNotNull($proxiedValue, 'proxiedValue is left over from working proxied iteration');

try {
	new Savvy_ObjectProxy_Traversable(null, $savvy);
} catch (UnexpectedValueException $e) {}
?>
===DONE===
--EXPECT--
===DONE===