--TEST--
Tests for rendering with filters
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

class ShoutingFilter extends Savvy_FilterAbstract
{
    public function __invoke($text)
    {
        return self::filter($text);
    }

    public static function filter($text)
    {
        return strtoupper(parent::filter($text));
    }
}

$savvy = new Savvy(array(
    'template_path' => __DIR__ . '/templates',
    'filters' => 'strtolower',
    'iterate_traversable' => false,
));

$object = new Foo();
$object->var1  = ' is my class';

try {
    $savvy->addGlobal('this', $object);
} catch (Savvy_BadMethodCallException $e) {}

$test->assertEquals(array(__DIR__ . '/templates/', './'), $savvy->getTemplatePath(), 'confirm template path construction');
$test->assertNull($savvy->getEscape(), 'escape should be empty');
$test->assertNotNull($savvy->getConfig('filters'), 'filters should not be empty through config getter');
$test->assertNull($savvy->getConfig('foo'), 'unset config should return null');
$test->assertNotNull($savvy->getConfig(), 'full config should not be null');
$test->assertNull($savvy->getTemplate(), 'null template when nothing has been rendered');
$test->assertEquals('unescaped', $savvy->escape('unescaped'), 'savvy should return unescaped when configured without and escape methods');

$savvy->addFilters(new ShoutingFilter(array('hello' => 'world')));
$savvy->addFilters();
$test->assertEquals(2, count($savvy->getFilters()), 'filter count should be 2 from construction and single valid add');

$test->assertEquals('FOO IS MY CLASS', $savvy->render($object), 'render object through filter');
$test->assertEquals('TEST', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template through filter');
$test->assertEquals('HELLO WORLD!', $savvy->render('HeLlo WoRlD!'), 'render string through filter');

$savvy->setEscape('addslashes');
$savvy->setFilters();
$test->assertEquals(array(), $savvy->getFilters(), 'filters should be empty');
$test->assertEquals('Savvy_ObjectProxy is my class', $savvy->render($object), 'render object through proxy after filters removed');
$test->assertEquals('test', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template');
$savvy->setTemplatePath();
$test->assertEquals(array('./'), $savvy->getTemplatePath(), 'savvy restored template path');

$savvy->setEscape();
$object->var1 = new Foo();
$test->assertEquals('FooFoo', $savvy->render($object), 'render object with child object');
?>
===DONE===
--EXPECT--
===DONE===