--TEST--
Savvy::addEscape() object variable escaping test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');

class Foo
{
    public $var1;
}

$object = new Foo();
$object->var1  = '<p></p>';

$test->assertEquals(htmlspecialchars($object->var1), $savvy->render($object), 'render object with variable escaping');

$test->assertEquals($object->var1, $savvy->render($object, 'raw.tpl.php'), 'render object with raw variable access');
?>
===DONE===
--EXPECT--
===DONE===