--TEST--
Savvy::render() array test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

$array = array(1,2,3);
$test->assertEquals('123', $savvy->render($array), 'render array');
$test->assertEquals('123', $savvy->render($array, 'echostring.tpl.php'), 'render array through custom template');

$confirmOriginalParam = true;
$originalParam = false;
$templateCallback = function($key, $value, $param) use ($test, $array, &$originalParam, &$confirmOriginalParam) {
	if ($confirmOriginalParam) {
		$confirmOriginalParam = false;
		$test->assertEquals($originalParam, $param, 'callback param is same without escaping');
	}
	$test->assertEquals($array[(int) $key], $value, 'key/value is same without escaping');
	return str_repeat(chr($key % 26 + 65), (int) ($key / 26) + 1) . $value;
};

$result = $savvy->renderAssocArray($array, $templateCallback);
$test->assertEquals('A1B2C3', $result, 'basic callback iteration render');

$originalParam = array('numbers' => array(3,2,1));
$confirmOriginalParam = true;
$result = $savvy->renderAssocArray($array, $originalParam, $templateCallback);
$test->assertEquals('A1B2C3', $result, $templateCallback);

//confirm escaping
$savvy->setEscape('htmlspecialchars')
	->setHTMLEscapeSettings(array('quotes' => ENT_QUOTES));
$array = array(
	'"BAD"' => '<p></p>',
	'GOOD' => array(
		'<a onclick="alert(\'injection\')"></a>'
	)
);
$settings = $savvy->getHTMLEscapeSettings();
$confirmOriginalParam = false;
$templateCallback = function($key, $value, $param) use ($test, $array, &$originalParam, &$confirmOriginalParam, $settings) {
	if ($confirmOriginalParam) {
		$confirmOriginalParam = false;
		$test->assertEquals(
			$originalParam,
			array(array(htmlspecialchars_decode($param[0][0], $settings['quotes']))),
			'callback param is same without escaping'
		);
	}
	$values = (array) $value;
	$value = implode(',', $values);
	$paramClass = '';
	foreach ((array) $param as $class) {
		$paramClass .= implode(',', (array) $class);
	}
	$test->assertEquals(
		implode(',', (array) $array[htmlspecialchars_decode($key, $settings['quotes'])]),
		htmlspecialchars_decode($value, $settings['quotes']),
		'key/value is escaped'
	);
	return "<div id=\"${key}\" class=\"${paramClass}\">${value}</div>\n";
};

$result = $savvy->renderAssocArray($array, $templateCallback);
$test->assertEquals(
	<<<'EOD'
<div id="&quot;BAD&quot;" class="">&lt;p&gt;&lt;/p&gt;</div>
<div id="GOOD" class="">&lt;a onclick=&quot;alert(&#039;injection&#039;)&quot;&gt;&lt;/a&gt;</div>
EOD
	,
	$result,
	'Fully escaped callback rendered array and array param'
);

$originalParam = array(array("Bad'O"));
$confirmOriginalParam = true;
$result = $savvy->renderAssocArray($array, $originalParam, $templateCallback);
$test->assertEquals(
	<<<'EOD'
<div id="&quot;BAD&quot;" class="Bad&#039;O">&lt;p&gt;&lt;/p&gt;</div>
<div id="GOOD" class="Bad&#039;O">&lt;a onclick=&quot;alert(&#039;injection&#039;)&quot;&gt;&lt;/a&gt;</div>
EOD
	,
	$result,
	'Fully escaped callback rendered array and array param'
);

try {
	$savvy->renderAssocArray($array, 'echostring.tpl.php');
} catch (Savvy_BadMethodCallException $e) {}
$test->assertNotNull($e, 'BadMethodCallException expected from invalid renderAssocArray signature');
?>
===DONE===
--EXPECT--
===DONE===