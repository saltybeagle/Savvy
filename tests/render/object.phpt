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

$test->assertEquals('Foo is my class', $savvy->render($object), 'render object');

$test->assertEquals('test', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template');

?>
===DONE===
--EXPECT--
===DONE===