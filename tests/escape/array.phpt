--TEST--
Escaping ArrayAccess
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);

$array = array();
$array[0] = '<h1></h1>';
$array[1] = '<p></p>';

$combined_raw_string = implode($array);

$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');

$array = $savvy->filterVar($array);

$test->assertEquals(htmlspecialchars($combined_raw_string), $savvy->render($array), 'render array forces iterating through template');
$test->assertEquals(htmlspecialchars($combined_raw_string), $savvy->render($array, 'ArrayAccessObject.tpl.php'), 'render array through template forces iterating');

try {
	new Savvy_ObjectProxy_ArrayObject($array, $savvy);
} catch (UnexpectedValueException $e) {}

?>
===DONE===
--EXPECT--
===DONE===