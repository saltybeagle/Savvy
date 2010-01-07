--TEST--
Savvy::render() fast compiler test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savvy = new Savvy();

class Foo
{
    public $var1;
    function __toString()
    {
        return 'test';
    }
}

$object = new Foo();
$object->var1  = ' is my class';

$savvy->setEscape();
mkdir(__DIR__ . '/compiled');
$compiler = new Savvy_BasicFastCompiler(__DIR__ . DIRECTORY_SEPARATOR . 'compiled');
$savvy->setCompiler($compiler);

$test->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR .
                    md5('.' . DIRECTORY_SEPARATOR . 'Foo.tpl.php'), $savvy->template('Foo.tpl.php'),
                    'verify compiler is called');
$test->assertEquals("<?php return '' .  get_class(\$context)  . '
' .  \$context->var1  . '';", file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR .
                    md5('.' . DIRECTORY_SEPARATOR . 'Foo.tpl.php')), 'compiled template');

$test->assertEquals('Foo is my class', $savvy->render($object), 'render object');

$test->assertEquals('test', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template');

?>
===DONE===
--CLEAN--
<?php
$a = opendir(__DIR__ . '/compiled');
while (false !== ($b = readdir($a))) {
    if (is_dir(__DIR__ . '/compiled/' . $b)) continue;
    unlink(__DIR__ . '/compiled/' . $b);
}
rmdir(__DIR__ . '/compiled');
?>
--EXPECT--
===DONE===