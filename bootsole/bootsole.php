<?php

// Set true for running unminified/merged CSS, false to run minified CSS.  Don't forget to reminify your CSS!
defined("CSS_DEV")
	or define("CSS_DEV", true);

// Set true for running unminified/merged JS, false to run minified JS.  Don't forget to reminify your JS!
defined("JS_DEV")
	or define("JS_DEV", true);

/***** Document (URL) paths ******/
defined("PUBLIC_ROOT")
    or define("PUBLIC_ROOT", "/bootsole/");

/***** File (local) paths ******/

defined("LOCAL_ROOT")
	or define ("LOCAL_ROOT", realpath(dirname(__FILE__)."/..") . "/");

defined("RESOURCES_ROOT")
    or define("RESOURCES_ROOT", realpath(dirname(__FILE__)) . "/");

defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", RESOURCES_ROOT . "templates/");

defined("PAGE_INCLUDES_SCHEMA_PATH")
    or define("PAGE_INCLUDES_SCHEMA_PATH", RESOURCES_ROOT . "schema/pages/pages.json");

defined("SITE_TITLE")
    or define("SITE_TITLE", "Bootsole");
    
// Set true to show missing (undefined) hooks in templates when rendering, false to replace them with an empty string
defined("OPTION_SHOW_MISSING_HOOKS")
	or define("OPTION_SHOW_MISSING_HOOKS", false);
    
require_once(RESOURCES_ROOT . "htmlbuilder/htmlbuilder.php");
require_once(RESOURCES_ROOT . "htmlbuilder/pagebuilder.php");
require_once(RESOURCES_ROOT . "htmlbuilder/componentbuilder.php");
require_once(RESOURCES_ROOT . "htmlbuilder/navbuilder.php");
require_once(RESOURCES_ROOT . "htmlbuilder/tablebuilder.php");
require_once(RESOURCES_ROOT . "htmlbuilder/formbuilder.php");
require_once(RESOURCES_ROOT . "validation/validation.php");

?>
