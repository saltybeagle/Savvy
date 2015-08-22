--TEST--
Escaping ArrayAccess
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);

class ArrayAccessObject extends ArrayObject
{
    function __toString()
    {
        return '<span></span>';
    }
}

$array = array();
$array[0] = '<h1></h1>';
$array[1] = '<p></p>';

$combined_raw_string = implode($array);
$array = new ArrayAccessObject($array);


$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');
$savvy->setIterateTraversable(false);
$test->assertEquals(htmlspecialchars((string)$array), $savvy->render($array), 'render ArrayAccess without iterating through template');
$test->assertEquals(htmlspecialchars((string)$array), $savvy->render($array, 'ArrayAccessObject.tpl.php'), 'render ArrayAccess through template without iterating');

// escaped ArrayObject should remain iterateable
foreach ($savvy->filterVar($array) as $proxiedValue) {
	echo $proxiedValue;
}
echo "\n";

?>
===DONE===
--EXPECT--
&lt;h1&gt;&lt;/h1&gt;&lt;p&gt;&lt;/p&gt;
===DONE===