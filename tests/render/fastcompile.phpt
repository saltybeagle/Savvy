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

class FaultyCompiler implements Savvy_CompilerInterface
{
    public function compile($name, $savvy)
    {
        echo 'This should not appear';
        return false;
    }
}

class StaticContentCompiler implements Savvy_CompilerInterface
{
    public function compile($name, $savvy)
    {
        $content = '<?php echo get_class($context) ?>';
        $cname = __DIR__ . '/staticcontentcompiler.tpl.php';
        file_put_contents($cname, $content);
        return $cname;
    }

    public function __destruct()
    {
        @unlink(__DIR__ . '/staticcontentcompiler.tpl.php');
    }
}

$object = new Foo();
$object->var1  = ' is my class';

$savvy->setEscape();
@mkdir(__DIR__ . '/compiled');
$compiler = new Savvy_BasicFastCompiler(__DIR__ . DIRECTORY_SEPARATOR . 'compiled');
$savvy->setCompiler($compiler);

$test->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR .
                    md5('.' . DIRECTORY_SEPARATOR . 'Foo.tpl.php'), $savvy->template('Foo.tpl.php'),
                    'verify compiler is called');
$test->assertEquals("<?php return '' .  get_class(\$context)  . '
' .  \$savvy->render(\$context->var1)  . '';", file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR .
                    md5('.' . DIRECTORY_SEPARATOR . 'Foo.tpl.php')), 'compiled template');

$test->assertEquals('Foo is my class', $savvy->render($object), 'render object');
$test->assertEquals('Foo is my class', $savvy->render($object), 'render object again');

$test->assertEquals('test', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template');

$savvy->addFilters('strtoupper');
$test->assertEquals('TEST', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template through filter');
$savvy->setCompiler(new StaticContentCompiler());
$test->assertEquals('FOO', $savvy->render($object, 'echostring.tpl.php'), 'render object with custom template through filter with compiler that creates static template');

try {
    $compiler = new Savvy_BasicFastCompiler('nowhere');
} catch (Savvy_UnexpectedValueException $e) {}

$compiler = new FaultyCompiler();
$savvy->setCompiler($compiler);
try {
    $savvy->render($object);
} catch (Savvy_TemplateException $e) {}

// test cleanup (should be in CLEAN section, but need to wait for PHPUnit support)
$a = opendir(__DIR__ . DIRECTORY_SEPARATOR . 'compiled');
while (false !== ($b = readdir($a))) {
    if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR . $b)) continue;
    unlink(__DIR__ . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR . $b);
}
rmdir(__DIR__ . DIRECTORY_SEPARATOR . 'compiled');
?>
===DONE===
--EXPECT--
===DONE===