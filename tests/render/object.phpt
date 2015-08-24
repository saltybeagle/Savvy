--TEST--
Savvy::render() object test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);

class Foo
{
    public $var1;
    function __toString()
    {
        return 'test';
    }
}

class StatFriendlySavvy extends Savvy
{
	protected $templateMap = array('./Foo.tpl.php' => true);
}

$object = new Foo();
$object->var1  = ' is my class';

$savvy = new StatFriendlySavvy();
$savvy->setEscape();

$test->assertEquals('Foo is my class', $savvy->render($object), 'render object');
$test->assertEquals('./Foo.tpl.php', $savvy->template('Foo.tpl.php'), 'savvy returns template from default path');
$test->assertEquals('test', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template');
$test->assertEquals('', $savvy->render(null, 'echostring.tpl.php'), 'render null with custom template');
$test->assertEquals('', $savvy->render(null), 'render null');

$savvy->addTemplatePath('fake://stream');
$test->assertEquals(2, count($savvy->getTemplatePath()), 'savvy supports adding streams to template path');
try {
	$savvy->addTemplatePath('../templates');
} catch (Savvy_UnexpectedValueException $e) {}
$test->assertEquals(2, count($savvy->getTemplatePath()), 'savvy throws when doing a directory traveral add to the template path');
?>
===DONE===
--EXPECT--
===DONE===