--TEST--
Savvy::render() string with addEscape() test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();
try {
	$savvy->setEscape('doesnotexist');
} catch (Savvy_UnexpectedValueException $ex) {
	echo 'Expected Exception Thrown' . "\n";
}


?>
===DONE===
--EXPECT--
Expected Exception Thrown
===DONE===