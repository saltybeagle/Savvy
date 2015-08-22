<?php
/**
 * Savvy_ClassToTemplateMapper class for Savvy
 *
 * This class allows class names to be mapped to template names though a simple
 * scheme.
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/saltybeagle/savvy
 */
class Savvy_ClassToTemplateMapper implements Savvy_MapperInterface
{
    /**
     * Default template mapping can be temporarily overridden by
     * assigning a direct template name.
     *
     * ClassToTemplateMapper::$output_template['My_Class'] = 'My/Class_rss.tpl.php';
     *
     * @var array
     */
    public static $output_template       = array();

    /**
     * What character to use as a directory separator when mapping class names
     * to templates.
     *
     * @var string
     */
    public static $directory_separator   = '_';

    /**
     * Strip something out of class names before mapping them to templates.
     *
     * This can be useful if your class names are very long, and you don't
     * want empty subdirectories within your templates directory.
     *
     * @var string
     */
    public static $classname_replacement = '';

    /**
     * The file extension to use
     *
     * @var string
     */
    public static $template_extension = '.tpl.php';

    /**
     * Maps class names to template filenames.
     *
     * This maps according to the PSR-0 standard -- 
     * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
     * 
     * Underscores in the class name and namespace separators are replaced with
     * directory separators.
     *
     * Examples:
     * Class           => Class.tpl.php
     * Other_Class     => Other/Class.tpl.php
     * namespace\Class => namespace/Class.tpl.php
     * name_space\Other_Class => name_space/Other/Class.tpl.php
     *
     * @param string $class Class name to map to a template
     *
     * @return string Template file name
     */
    public function map($class)
    {
        if (isset(self::$output_template[$class])) {
            $class = self::$output_template[$class];
        }

        $class = str_replace(self::$classname_replacement, '', $class);

        $className = ltrim($class, '\\');
        $fileName  = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace(self::$directory_separator, DIRECTORY_SEPARATOR, $className) . self::$template_extension;

        return $fileName;
    }

}
