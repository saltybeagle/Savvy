--TEST--
Savvy::render() non-iterateable scalar test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();
$savvy->setEscape('htmlspecialchars');

$string = 'test';
$test->assertEquals($string, $savvy->render($string), 'render');

$resource = fopen('echostring.tpl.php', 'r');
try {
	$savvy->render($resource);
} catch (Savvy_UnexpectedValueException $ex) {
	echo 'Expected Exception Thrown' . "\n";
}
fclose($resource);

$test->assertEquals('', $savvy->render(false), 'render FALSE');
$test->assertEquals('1', $savvy->render(true), 'render TRUE');

$test->assertEquals('1', $savvy->render(1.), 'render float');
$test->assertEquals('1', $savvy->render(1), 'render int');

$test->assertEquals('true', $savvy->renderElse(true, 'true', 'false'), 'render conditional true');
$test->assertEquals('false', $savvy->renderElse(false, 'true', 'false'), 'render conditional false');

$string = '<p></p>';
$test->assertEquals(htmlspecialchars($string), $savvy->render($string), 'render string with special chars');

$string = 'test';
$test->assertEquals($string, $savvy->render($string, 'echostring.tpl.php'), 'render string through template');

$string = '<p></p>';
$test->assertEquals(htmlspecialchars($string), $savvy->render($string, 'echostring.tpl.php'), 'render string with special chars through template');

try {
	$savvy->template('doesNotExist.tpl.php');
} catch (Savvy_TemplateException $e) {}

try {
	$savvy->findTemplateFile('../doesNotExist.tpl.php');
} catch (Savvy_UnexpectedValueException $e) {}
?>
===DONE===
--EXPECT--
Expected Exception Thrown
===DONE===