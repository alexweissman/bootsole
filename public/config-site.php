<?php

/********* Project-specific constants - feel free to put whatever you need here ********/

defined("SITE_TITLE")
    or define("SITE_TITLE", "Bootsole");


/********* Try to use Composer autoloader to include files.  If not found, simply require each file manually. ********/
$autoloader_path = __DIR__.'/../vendor/autoload.php';

if (!file_exists($autoloader_path)){
    $config_path = __DIR__.'/../bootsole/config-bootsole.php';
    require_once($config_path);

    $class_files = [
        "validation/validation.php",       
        "htmlbuilder/HtmlBuilder.php",
        "htmlbuilder/ComponentBuilder.php",
        "htmlbuilder/NavBuilder.php",
        "htmlbuilder/PageBuilder.php",
        "htmlbuilder/TableBuilder.php",
        "htmlbuilder/FormBuilder.php"     
    ];

    foreach ($class_files as $filename) {
        require_once Bootsole\PATH_BOOTSOLE_ROOT . $filename;
    }
} else {
    $loader = require $autoloader_path;
    $loader->add('Bootsole', __DIR__);
}

?>
