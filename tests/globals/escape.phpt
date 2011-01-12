--TEST--
Savvy::addGlobal() Escape added globals test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

function myEscape($var)
{
    echo '===DONE===';
    return $var;
}

$savvy->setEscape('myEscape');

$savvy->addGlobal('foo', 'lalalala');

?>
--EXPECT--
===DONE===