--TEST--
Savvy::addGlobal() basic test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

$savvy->addGlobal('foo', true);

echo $savvy->render(null, 'basic.tpl.php');

?>
--EXPECT--
===DONE===