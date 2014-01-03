--TEST--
ClassToTemplateMapper test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$mapper = new Savvy_ClassToTemplateMapper();
$test->assertEquals('Class.tpl.php', $mapper->map('Class'), 'Map with no namespace');
$test->assertEquals('Other/Class.tpl.php', $mapper->map('Other_Class'), 'Map with underscore and no namespace');
$test->assertEquals('namespace/Class.tpl.php', $mapper->map('namespace\Class'), 'Map with namespace');
$test->assertEquals('name_space/Other/Class.tpl.php', $mapper->map('name_space\Other_Class'), 'map with underscore in both namespace and class name');

?>
===DONE===
--EXPECT--
===DONE===