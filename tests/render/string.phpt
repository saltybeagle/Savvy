--TEST--
Savvy::render() string test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');

$string = 'test';
$test->assertEquals($string, $savvy->render($string), 'render');

$string = '<p></p>';
$test->assertEquals(htmlspecialchars($string), $savvy->render($string), 'render string with special chars');

$string = 'test';
$test->assertEquals($string, $savvy->render($string, 'echostring.tpl.php'), 'render string through template');

$string = '<p></p>';
$test->assertEquals(htmlspecialchars($string), $savvy->render($string, 'echostring.tpl.php'), 'render string with special chars through template');
?>
===DONE===
--EXPECT--
===DONE===