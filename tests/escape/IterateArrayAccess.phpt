--TEST--
Escaping ArrayAccess
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);

class ArrayAccessObject extends ArrayIterator{
    
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
$savvy->setIterateTraversable(true);
$test->assertEquals(htmlspecialchars($combined_raw_string), $savvy->render($array), 'render ArrayAccess with iterating');
//$test->assertEquals(htmlspecialchars($combined_raw_string), $savvy->render($array, 'ArrayAccessObject.tpl.php'), 'render ArrayAccess through template with iterating');
?>
===DONE===
--EXPECT--
===DONE===