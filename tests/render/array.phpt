--TEST--
Savvy::render() array test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

$array = array(1,2,3);
$test->assertEquals('123', $savvy->render($array), 'render array');

$array = array(1,2,3);
$test->assertEquals('123', $savvy->render($array, 'echostring.tpl.php'), 'render array through custom template');

?>
===DONE===
--EXPECT--
===DONE===