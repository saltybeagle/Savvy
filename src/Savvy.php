<?php
/**
 * Main class for Savvy
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/saltybeagle/savvy
 */
class Savvy
{
    /**
     * Array of configuration parameters.
     *
     * @var array
     */
    protected $__config = array(
        'compiler' => null,
        'filters' => array(),
        'escape' => null,
        'iterate_traversable' => false,
    );

    /**
     * Parameters for escaping.
     *
     * @var array
     */
    protected $_escape = array(
        'quotes'  => ENT_COMPAT,
        'charset' => 'UTF-8',
        'double_encode' => false,
    );

    /**
     * The output template to render using
     *
     * @var string
     */
    protected $template;

    /**
     * stack of templates, so we can access the parent template
     *
     * @var array
     */
    protected $templateStack = array();

    /**
     * To avoid stats on locating templates, populate this array with
     * full path => 1 for any existing templates
     *
     * @var array
     */
    protected $templateMap = array();

    /**
     * An array of paths to look for template files in.
     *
     * @var array
     */
    protected $template_path = array('./');

    /**
     * The current controller to use
     *
     * @var string
     */
    protected $selected_controller;

    /**
     * How class names are translated to templates
     *
     * @var Savvy_MapperInterface
     */
    protected $class_to_template;

    /**
     * Array of globals available within every template
     *
     * @var array
     */
    protected $globals = array();

    // -----------------------------------------------------------------
    //
    // Constructor and magic methods
    //
    // -----------------------------------------------------------------

    /**
     * Constructor.
     *
     * @param array $config An associative array of configuration keys for
     * the Savvy object.  Any, or none, of the keys may be set.
     */
    public function __construct($config = null)
    {
        // set the default template search path
        if (isset($config['template_path'])) {
            // user-defined dirs
            $this->setTemplatePath($config['template_path']);
        }

        // set the output escaping callbacks
        if (isset($config['escape'])) {
            $this->setEscape($config['escape']);
        }

        // set the default filter callbacks
        if (isset($config['filters'])) {
            $this->setFilters($config['filters']);
        }

        // set whether to iterate over Traversable objects
        if (isset($config['iterate_traversable'])) {
            $this->setIterateTraversable($config['iterate_traversable']);
        }

        if (defined('ENT_HTML5')) {
            $this->_escape['quotes'] |= ENT_HTML5;
        }

        $this->selectController();
    }

    /**
     * Basic output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function basicOutputController($context, $parent, $file, Savvy $savvy)
    {
        try {
            extract($savvy->getGlobals(true));
            ob_start();
            include $file;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Filter output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function filterOutputController($context, $parent, $file, Savvy $savvy)
    {
        return $savvy->applyFilters(static::basicOutputController($context, $parent, $file, $savvy));
    }

    /**
     * Basic Compiled output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function basicCompiledOutputController($context, $parent, $file, Savvy $savvy)
    {
        try {
            extract($savvy->getGlobals(true));
            ob_start();
            include $savvy->template($file);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Filter Compiled output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function filterCompiledOutputController($context, $parent, $file, Savvy $savvy)
    {
        return $savvy->applyFilters(static::basicCompiledOutputController($context, $parent, $file, $savvy));
    }

    /**
     * Basic Fast Compiled output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function basicFastCompiledOutputController($context, $parent, $file, Savvy $savvy)
    {
        extract($savvy->getGlobals(true));
        return include $savvy->template($file);
    }

    /**
     * Filter Fast Compiled output controller
     *
     * @param mixed $context The context passed to the template
     * @param mixed $parent  Parent template with context and parents $parent->context
     * @param mixed $file    The filename to include
     * @param Savvy $savvy   The Savvy templating system
     * @return string
     */
    protected static function filterFastCompiledOutputController($context, $parent, $file, Savvy $savvy)
    {
        return $savvy->applyFilters(static::basicFastCompiledOutputController($context, $parent, $file, $savvy));
    }

    /**
     * Add a global variable which will be available inside every template
     *
     * Inside templates, reference the global using the name passed
     * <code>
     * $savvy->addGlobal('formHelper', new FormHelper());
     * </code>
     *
     * Sample template, Form.tpl.php
     * <code>
     * echo $formHelper->renderInput('name');
     * </code>
     *
     * @param string $var   The global variable name
     * @param mixed  $value The value or variable to expose globally
     * @return void
     */
    public function addGlobal($name, $value)
    {
        // disallow specific variable names, these are reserved variables
        switch ($name) {
            case 'context':
            case 'parent':
            case 'template':
            case 'savvy':
            case 'this':
                throw new Savvy_BadMethodCallException('Invalid global variable name');
        }

        $this->globals[$name] = $value;
    }

    /**
     * Filter a variable of unknown type
     *
     * @param mixed $var The variable to filter
     * @return string|Savvy_ObjectProxy
     */
    public function filterVar($var)
    {
        switch (gettype($var)) {
            case 'array':
                // array will be proxied through special ObjectProxy that supports directly rendering elements through iteration
            case 'object':
                return Savvy_ObjectProxy::factory($var, $this);
            case 'string':
            case 'integer':
            case 'double':
                return $this->escape($var);
        }

        return $var;
    }

    /**
     * Returns the array with all keys and values (fully recursive) escaped.
     *
     * @param array $array
     * @return array
     */
    protected function filterArray(array $array)
    {
        $escapedArray = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->filterArray($value);
            } else {
                $value = $this->filterVar($value);
            }

            $escapedArray[$this->filterVar($key)] = $value;
        }

        return $escapedArray;
    }

    /**
     * Get the array of assigned globals
     *
     * @return array
     */
    public function getGlobals($forOutput = false)
    {
        if (!$forOutput || !$this->__config['escape']) {
            return $this->globals;
        }

        $outputGlobals = array();
        foreach ($this->globals as $name => $value) {
            $value = $this->filterVar($value);
            $outputGlobals[$name] = $value;
        }

        return $outputGlobals;
    }

    /**
     * Return the current template set (if any)
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    // -----------------------------------------------------------------
    //
    // Public configuration management (getters and setters).
    //
    // -----------------------------------------------------------------

    /**
     * Returns a copy of the Savvy configuration parameters.
     *
     * @param string $key The specific configuration key to return.  If null,
     * returns the entire configuration array.
     * @return mixed A copy of the $this->__config array.
     */
    public function getConfig($key = null)
    {
        if (is_null($key)) {
            // no key requested, return the entire config array
            return $this->__config;
        } elseif (empty($this->__config[$key])) {
            // no such key
            return null;
        } else {
            // return the requested key
            return $this->__config[$key];
        }
    }

    /**
     * Sets a custom compiler/pre-processor callback for template sources.
     *
     * By default, Savvy does not use a compiler; use this to set your
     * own custom compiler (pre-processor) for template sources.
     *
     * @param Savvy_CompilerInterface $compiler A compiler callback value suitable for the
     * first parameter of call_user_func().  Set to null/false/empty to
     * use PHP itself as the template markup (i.e., no compiling).
     * @return $this
     *
     */
    public function setCompiler(Savvy_CompilerInterface $compiler)
    {
        $this->__config['compiler'] = $compiler;
        $this->selectController();

        return $this;
    }

    /**
     * Set the class to template mapper.
     *
     * @param Savvy_MapperInterface $mapper The mapper interface to use
     * @return $this
     */
    public function setClassToTemplateMapper(Savvy_MapperInterface $mapper)
    {
        $this->class_to_template = $mapper;

        return $this;
    }

    /**
     * Get the class to template mapper.
     *
     * @return Savvy_MapperInterface
     */
    public function getClassToTemplateMapper()
    {
        if (!isset($this->class_to_template)) {
            $this->setClassToTemplateMapper(new Savvy_ClassToTemplateMapper());
        }

        return $this->class_to_template;
    }

    /**
     * Set the configuration flag to enable automatic Traversable rendering
     *
     * @param bool $iterate The flag
     * @return $this
     */
    public function setIterateTraversable($iterate)
    {
        $this->__config['iterate_traversable'] = (bool)$iterate;

        return $this;
    }

    /**
     * Return the configuration flag to enable automatic Traversable rendering
     *
     * @return bool
     */
    public function getIterateTraversable()
    {
        return $this->__config['iterate_traversable'];
    }

    /**
     * Check to ensure all given callables are actually callable
     *
     * @param Callable[] $callables
     * @return $this
     */
    protected function validateCallbacks($callables)
    {
        foreach ($callables as $callable) {
            if (!is_callable($callable)) {
                throw new Savvy_UnexpectedValueException('All parameters must be callable');
            }
        }

        return $this;
    }

    // -----------------------------------------------------------------
    //
    // Output escaping and management.
    //
    // -----------------------------------------------------------------

    /**
     * Clears then sets the callbacks to use when calling $this->escape().
     *
     * Each parameter passed to this function is treated as a separate
     * callback.  For example:
     *
     * <code>
     * $savvy->setEscape(
     *     'stripslashes',
     *     'htmlspecialchars',
     *     array('StaticClass', 'method'),
     *     array($object, $method)
     * );
     * </code>
     *
     * @return $this
     */
    public function setEscape()
    {
        $callables = @func_get_args();
        if (!$callables) {
            $this->__config['escape'] = $callables;
            return $this;
        }

        $this->validateCallbacks($callables);
        $this->__config['escape'] = $callables;

        return $this;
    }

    /**
     * Gets the array of output-escaping callbacks.
     *
     * @return array The array of output-escaping callbacks.
     */
    public function getEscape()
    {
        return $this->__config['escape'];
    }

    /**
     * Changes the default settings used during standard HTML escaping
     * (htmlentities/htmlspecialchars).
     *
     * Currently supported array keys are <code>"quotes"</code> (which maps to the
     * <code>$flags</code> parameter), <code>"charset"</code> (which maps to
     * the <code>$encoding</code> parameter) and <code>"double_encoding"</code>
     * (which maps to the similarly named parameter).
     *
     * @param array $settings
     * @return $this
     */
    public function setHTMLEscapeSettings(array $settings)
    {
        $this->_escape = array_merge($this->_escape, array_intersect_key($settings, $this->_escape));

        return $this;
    }

    /**
     * Returns the current settings used during standard HTML escaping
     * (htmlentities/htmlspecialchars).
     *
     * @return array
     */
    public function getHTMLEscapeSettings()
    {
        return $this->_escape;
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_escape} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (!$this->__config['escape']) {
            return $var;
        }

        foreach ($this->__config['escape'] as $escape) {
            if (in_array($escape, array('htmlspecialchars', 'htmlentities'), true)) {
                $var = call_user_func($escape, $var, $this->_escape['quotes'], $this->_escape['charset'], $this->_escape['double_encode']);
            } else {
                $var = call_user_func($escape, $var);
            }
        }

        return $var;
    }

    // -----------------------------------------------------------------
    //
    // File management
    //
    // -----------------------------------------------------------------

    /**
     * Get the template path.
     *
     * @return array
     */
    public function getTemplatePath()
    {
        return $this->template_path;
    }

    /**
     * Sets an entire array of search paths for templates or resources.
     *
     * @param string|array $path The new set of search paths.  If null or
     * false, resets to the current directory only.
     * @return $this
     */
    public function setTemplatePath($path = null)
    {
        // clear out the prior search dirs, add default
        $this->template_path = array('./');

        // actually add the user-specified directories
        $this->addTemplatePath($path);

        return $this;
    }

    /**
     * Adds to the search path for templates and resources.
     *
     * @param string|array $path The directory or stream to search.
     * @return $this
     */
    public function addTemplatePath($path)
    {
        // convert from path string to array of directories
        if (is_string($path) && !strpos($path, '://')) {

            // the path config is a string, and it's not a stream
            // identifier (the "://" piece). add it as a path string.
            $path = explode(PATH_SEPARATOR, $path);

            // typically in path strings, the first one is expected
            // to be searched first. however, Savvy uses a stack,
            // so the first would be last.  reverse the path string
            // so that it behaves as expected with path strings.
            $path = array_reverse($path);

        } else {

            // just force to array
            settype($path, 'array');

        }

        // loop through the path directories
        foreach ($path as $dir) {

            // no surrounding spaces allowed!
            $dir = trim($dir);

            // add trailing separators as needed
            if (strpos($dir, '://')) {
                if (substr($dir, -1) != '/') {
                    // stream
                    $dir .= '/';
                }
            } elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
                if (false !== strpos($dir, '..')) {
                    // checking for weird paths here removes directory traversal threat
                    throw new Savvy_UnexpectedValueException('upper directory reference .. cannot be used in template path');
                }
                // directory
                $dir .= DIRECTORY_SEPARATOR;
            }

            // add to the top of the search dirs
            array_unshift(
                $this->template_path,
                $dir
            );
        }
    }


    /**
     * Searches the directory paths for a given file.
     *
     * @param string $file The file name to look for.
     * @return string|bool The full path and file name for the target file,
     * or boolean false if the file is not found in any of the paths.
     */
    public function findTemplateFile($file)
    {
        if (false !== strpos($file, '..')) {
            // checking for weird path here removes directory traversal threat
            throw new Savvy_UnexpectedValueException('upper directory reference .. cannot be used in template filename');
        }

        // start looping through the path set
        foreach ($this->template_path as $path) {
            // get the path to the file
            $fullname = $path . $file;

            if (isset($this->templateMap[$fullname])) {
                return $fullname;
            }

            if (!@is_readable($fullname)) {
                continue;
            }

            return $fullname;
        }

        // could not find the file in the set of paths
        throw new Savvy_TemplateException('Could not find the template ' . $file);
    }


    // -----------------------------------------------------------------
    //
    // Template processing
    //
    // -----------------------------------------------------------------

    /**
     * Return the original object if the given object is a proxy
     *
     * @param mixed $object
     * @return mixed
     */
    public function getRawObject($object)
    {
        $rawObject = $object;

        if ($object instanceof Savvy_ObjectProxy) {
            $rawObject = $object->getRawObject();
        }

        return $rawObject;
    }

    /**
     * Render context data through a template.
     *
     * This method allows you to render data through a template. Typically one
     * will pass the model they wish to display through an optional template.
     * If no template is specified, the ClassToTemplateMapper::map() method
     * will be called which should return the name of a template to render.
     *
     * Arrays will be looped over and rendered through the template specified.
     *
     * Strings, ints, and doubles will returned if no template parameter is
     * present.
     *
     * Within templates, two variables will be available, $context and $savvy.
     * The $context variable will contain the data passed to the render method,
     * the $savvy object will be an instance of the Main class with which you
     * can render nested data through partial templates.
     *
     * @param mixed  $mixed    Data to display through the template.
     * @param string $template A template to display data in.
     * @return string The template output
     */
    public function render($mixed = null, $template = null)
    {
        $method = 'render'.gettype($mixed);

        return $this->$method($mixed, $template);
    }

    /**
     * Called when a resource is rendered
     *
     * @param resource $resouce  The resources
     * @param string   $template Template
     * @return void
     * @throws UnexpectedValueException
     */
    protected function renderResource($resouce, $template = null)
    {
        throw new Savvy_UnexpectedValueException('No way to render a resource!');
    }

    protected function renderBoolean($bool, $template = null)
    {
        return $this->renderString((string) $bool, $template);
    }

    protected function renderDouble($double, $template = null)
    {
        return $this->renderString($double, $template);
    }

    protected function renderInteger($int, $template = null)
    {
        return $this->renderString($int, $template);
    }

    /**
     * Render string of data
     *
     * @param string $string   String of data
     * @param string $template A template to display the string in
     * @return string
     */
    protected function renderString($string, $template = null)
    {
        if ($this->__config['escape']) {
            $string = $this->escape($string);
        }

        if ($template) {
            return $this->fetch($string, $template);
        }

        if (!$this->__config['filters']) {
            return $string;
        }

        return $this->applyFilters($string);
    }

    /**
     * Used to render context array
     *
     * @param array  $array    Data to render
     * @param string $template Template to render
     * @return string Rendered output
     */
    protected function renderArray(array $array, $template = null)
    {
        return $this->renderTraversable($array);
    }

    /**
     * Render an iterable object/array using the given template
     *
     * @param array|Traversable $array Data to render
     * @param string $template Template to render
     * @return string
     */
    protected function renderTraversable($array, $template = null)
    {
        $ret = '';
        foreach ($array as $element) {
            $ret .= $this->render($element, $template);
        }

        return $ret;
    }

    /**
     * Render an if else conditional template output.
     *
     * @param mixed  $condition      The conditional to evaluate
     * @param mixed  $render         Context data to render if condition is true
     * @param mixed  $else           Context data to render if condition is false
     * @param string $rendertemplate If true, render using this template
     * @param string $elsetemplate   If false, render using this template
     * @return string
     */
    public function renderElse($condition, $render, $else, $rendertemplate = null, $elsetemplate = null)
    {
        if ($condition) {
            return $this->render($render, $rendertemplate);
        } else {
            return $this->render($else, $elsetemplate);
        }
    }

     /**
      * Render an associative array of data through a template closure/callback.
      *
      * Three parameters will be passed to the closure, the array key, value,
      * and selective third parameter. All will be properly escaped, if so configured.
      *
      * @param array   $array    Associative array of data
      * @param mixed   $selected Optional Parameter to pass
      * @param Closure $template A closure that will be called
      * @return string
      * @throws
      */
    public function renderAssocArray(array $array, $selected, Closure $template = null)
    {
        // backwards compatible signature where selected is optional
        if (null === $template) {
            if ($selected instanceof Closure) {
                $template = $selected;
                $selected = false;
            } else {
                throw new Savvy_BadMethodCallException('renderAssocArray must be called with a Closure to callback with the rendered array element');
            }
        }

        $ret = '';
        foreach ($array as $key => $element) {
            $ret .= $this->renderWithCallback($element, $template, $key, $selected);
        }

        return $ret;
    }

    /**
     * Render the given <code>$mixed</code> and pass it to a callback/closure
     * with escaped <code>$key</code> and <code>$paramToEscape</code>
     *
     * @param mixed $mixed
     * @param Closure $template
     * @param int|string $key
     * @param mixed $paramToEscape
     */
    public function renderWithCallback($mixed, Closure $template, $key = false, $paramToEscape = false)
    {
        if ($this->__config['escape']) {
            $key = $this->filterVar($key);

            // special handling for arrays, the Closure is probably not expecting a proxy for this scalar

            if (is_array($paramToEscape)) {
                $paramToEscape = $this->filterArray($paramToEscape);
            } else {
                $paramToEscape = $this->filterVar($paramToEscape);
            }

            if (is_array($mixed)) {
                $mixed = $this->filterArray($mixed);
            } else {
                $mixed = $this->filterVar($mixed);
            }
        }

        return $template($key, $mixed, $paramToEscape);
    }

    /**
     * Used to render an object through a template.
     *
     * @param object $object   Model containing data
     * @param string $template Template to render data through
     *
     * @return string Rendered output
     */
    protected function renderObject($object, $template = null)
    {
        if ($this->__config['escape']) {
            if (!$object instanceof Savvy_ObjectProxy) {
                $object = Savvy_ObjectProxy::factory($object, $this);
            }

            if ($object instanceof Traversable && $this->__config['iterate_traversable'] ||
                $object instanceof Savvy_ObjectProxy_ArrayObject
            ) {
                return $this->renderTraversable($object->getRawObject(), $template);
            }
        } elseif ($object instanceof Savvy_ObjectProxy) {
            $object = $object->getRawObject();
        }

        return $this->fetch($object, $template);
    }

    /**
     * Used to render null through an optional template
     *
     * @param null   $null     The null var
     * @param string $template Template to render null through
     *
     * @return string Rendered output
     */
    protected function renderNULL($null, $template = null)
    {
        if ($template) {
            return $this->fetch(null, $template);
        }

        return '';
    }

    /**
     * Renders the given template using the selected output conroller with the given context
     *
     * @param mixed $mixed The context for the renered template
     * @param string $template The template name to find a template file from
     * @return string
     */
    protected function fetch($mixed, $template = null)
    {
        if ($template) {
            $this->template = $template;
        } else {
            if ($mixed instanceof Savvy_ObjectProxy) {
                $class = $mixed->__getClass();
            } else {
                $class = get_class($mixed);
            }
            $this->template = $this->getClassToTemplateMapper()->map($class);
        }

        $current = array(
            'file' => $this->findTemplateFile($this->template),
            'context' => $mixed,
            'parent' => null,
        );

        $templateStackSize = count($this->templateStack);
        if ($templateStackSize) {
            $current['parent'] = $this->templateStack[$templateStackSize - 1];
        }

        $current = (object) $current;
        $this->templateStack[] = $current;

        try {
            $ret = call_user_func(
                array($this, $this->selected_controller.'OutputController'),
                $current->context,
                $current->parent,
                $current->file,
                $this
            );
        } catch (Exception $e) {
            array_pop($this->templateStack);
            throw $e;
        }

        array_pop($this->templateStack);

        return $ret;
    }

    /**
     * Compiles a template and returns path to compiled script.
     *
     * By default, Savvy does not compile templates, it uses PHP as the
     * markup language, so the "compiled" template is the same as the source
     * template.
     *
     * If a compiler is specific, this method is used to look up the compiled
     * template script name
     *
     * @param string $tpl The template source name to look for.
     * @return string The full path to the compiled template script.
     * @throws Savvy_UnexpectedValueException
     * @throws Savvy_Exception
     */
    public function template($tpl = null)
    {
        // find the template source.
        $file = $this->findTemplateFile($tpl);

        // are we compiling source into a script?
        if ($this->__config['compiler']) {
            // compile the template source and get the path to the
            // compiled script (will be returned instead of the
            // source path)
            $result = $this->__config['compiler']->compile($file, $this);
        } else {
            // no compiling requested, use the source path
            $result = $file;
        }

        // is there a script from the compiler?
        if (!$result) {
            // return an error, along with any error info
            // generated by the compiler.
            throw new Savvy_TemplateException('Compiler error for template '.$tpl.'. '.$result );

        } else {
            // no errors, the result is a path to a script
            return $result;
        }
    }

    /**
     * Assigns the appropriate output controller state based on current configuration
     *
     * @return $this
     */
    protected function selectController()
    {
        if (!$this->__config['filters']) {
            $controller = 'basic';
        } else {
            $controller = 'filter';
        }

        $compiler = $this->__config['compiler'];
        if ($compiler) {
            if ($compiler instanceof Savvy_FastCompilerInterface) {
                $controller .= 'Fast';
            }

            $controller .= 'Compiled';
        }

        $this->selected_controller = $controller;

        return $this;
    }

    // -----------------------------------------------------------------
    //
    // Filter management and processing
    //
    // -----------------------------------------------------------------

    public function getFilters()
    {
        return $this->__config['filters'];
    }

    /**
     * Resets the filter stack to the provided list of callbacks.
     *
     * @param array An array of filter callbacks.
     * @return $this
     */
    public function setFilters()
    {
        $callables = (array) @func_get_args();
        if (!$callables) {
            $this->__config['filters'] = array();
            return $this;
        }

        $this->validateCallbacks($callables);
        $this->__config['filters'] = $callables;
        $this->selectController();

        return $this;
    }


    /**
     * Adds filter callbacks to the stack of filters.
     *
     * @param array An array of filter callbacks.
     * @return $this
     */
    public function addFilters()
    {
        $callables = @func_get_args();
        if (!$callables) {
            return $this;
        }

        $this->validateCallbacks($callables);
        $this->__config['filters'] = array_merge($this->__config['filters'], $callables);
        $this->selectController();

        return $this;
    }

    /**
     * Runs all filter callbacks on buffered output.
     *
     * @param string The template output.
     * @return string
     */
    public function applyFilters($buffer)
    {
        if (!$this->__config['filters']) {
            return $buffer;
        }

        foreach ($this->__config['filters'] as $callback) {
            $buffer = call_user_func($callback, $buffer);
        }

        return $buffer;
    }

}
