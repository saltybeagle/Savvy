--TEST--
Savvy::render() string with addEscape() test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');

$string = 'test';
$test->assertEquals($string, $savvy->escape($string), 'render');

$string = '<p></p>';
$test->assertEquals(htmlspecialchars($string), $savvy->escape($string), 'render string with special chars');

?>
===DONE===
--EXPECT--
===DONE===