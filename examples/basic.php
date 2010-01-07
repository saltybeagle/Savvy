<?php
ini_set('display_errors',true);
error_reporting(E_ALL^E_STRICT);
require_once dirname(__FILE__).'/../src/Savvy/Autoload.php';
$classLoader = new Savvy_Autoload();
$classLoader->register();

// Set up a view object we'd like to display
$class = new stdClass();
$class->var1 = '<p>This is var1 inside a standard class</p>';

$savvy = new Savvy();
$savvy->addTemplatePath(__DIR__ . '/templates');

// Display a simple string
echo $savvy->render('<h1>Welcome to the Savvy Demo</h1>');

// Display a string, in a custom template
echo $savvy->render('mystring', 'StringView.tpl.php');

// Display an array
echo $savvy->render(array('<ul>', '<li>This is an array</li>', '</ul>'));

// Display an object using a default class name to template mapping function
echo $savvy->render($class);

// Display the object using a specific template
echo $savvy->render($class, 'MyTemplate.tpl.php');

echo $savvy->render('<h2>Output Filtering</h2>');
$savvy->addFilters('htmlspecialchars');

// Now show an entire template with htmlspecialchars
echo $savvy->render($class);

// Ok, now remove the output filters
$savvy->setFilters();

highlight_file(__FILE__);

