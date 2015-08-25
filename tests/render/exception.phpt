--TEST--
Savvy::render() object test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

class Foo
{
    public $var1;
    function __toString()
    {
        return 'test';
    }
}

$object = new Foo();
$object->var1  = ' is my class';

$savvy->setEscape();

try {
	$test->assertEquals('test', $savvy->render($object, 'badexception.tpl.php'), 'render with exception should not have side-effects');
} catch (Exception $e) {
	echo 'Expected Exception Thrown' . "\n";
}

$test->assertEquals('1', $savvy->render($object, 'Foo-stack.tpl.php'), 'render object after exceptions should not have stack side-effects');

?>
===DONE===
--EXPECT--
Expected Exception Thrown
===DONE===