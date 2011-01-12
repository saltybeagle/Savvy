--TEST--
Savvy::addGlobal() Escape added global array
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

$savvy->setEscape('htmlspecialchars');

$unescaped = array(
    '<a href="Blah">Blah</a>'
);

$savvy->addGlobal('foo', $unescaped);

$escaped = $savvy->getGlobals();

echo $escaped['foo'][0];

?>
--EXPECT--
&lt;a href=&quot;Blah&quot;&gt;Blah&lt;/a&gt;