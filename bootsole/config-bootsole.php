<?php

namespace Bootsole;

/***** Document (URI) paths ******/

/* The public URI corresponding to the document root of your website. */
defined("Bootsole\URI_PUBLIC_ROOT")
    or define("Bootsole\URI_PUBLIC_ROOT", "http://localhost/bootsole/public/");

/* The public URI corresponding to the Javascript assets of your website. */
defined("Bootsole\URI_JS_ROOT")
    or define("Bootsole\URI_JS_ROOT", URI_PUBLIC_ROOT . "js/");    

/* The public URI corresponding to the CSS assets of your website. */
defined("Bootsole\URI_CSS_ROOT")
    or define("Bootsole\URI_CSS_ROOT", URI_PUBLIC_ROOT . "css/");
    
/***** File (local) paths ******/

/* The root directory of your public web assets (e.g. 'public', 'public_html', etc) */
defined("Bootsole\PATH_PUBLIC_ROOT")
	or define ("Bootsole\PATH_PUBLIC_ROOT", realpath(dirname(__FILE__) . "/../public") . "/");

/* The root directory of your Javascript assets */    
defined("Bootsole\PATH_JS_ROOT")
	or define ("Bootsole\PATH_JS_ROOT", PATH_PUBLIC_ROOT . "js/");  

/* The root directory of your CSS assets */
defined("Bootsole\PATH_CSS_ROOT")
	or define ("Bootsole\PATH_CSS_ROOT", PATH_PUBLIC_ROOT . "css/");
    
/* The root directory in which the Bootsole resources reside.  Should usually be the same directory that this config file resides in.*/
defined("Bootsole\PATH_BOOTSOLE_ROOT")
    or define("Bootsole\PATH_BOOTSOLE_ROOT", realpath(dirname(__FILE__)) . "/");

/* The root directory in which the Bootsole templates reside. */
defined("Bootsole\PATH_TEMPLATES")
    or define("Bootsole\PATH_TEMPLATES", PATH_BOOTSOLE_ROOT . "templates/");

/* The root directory in which the Bootsole schema reside. */
defined("Bootsole\PATH_SCHEMA")
    or define("Bootsole\PATH_SCHEMA", PATH_BOOTSOLE_ROOT . "schema/");

/* The default page schema (for determining CSS/JS includes in PageHeaderBuilder and PageFooterBuilder). */
defined("Bootsole\FILE_SCHEMA_PAGE_DEFAULT")
    or define("Bootsole\FILE_SCHEMA_PAGE_DEFAULT", PATH_SCHEMA . "pages/pages.json");

    
/***** Config options ******/
       
// Set true to show missing (undefined) hooks in templates when rendering, false to replace them with an empty string
defined("Bootsole\OPTION_SHOW_MISSING_HOOKS")
	or define("Bootsole\OPTION_SHOW_MISSING_HOOKS", false);

// Set true for running unminified/merged CSS, false to run minified CSS.  Don't forget to reminify your CSS!
defined("Bootsole\CSS_DEV")
	or define("Bootsole\CSS_DEV", true);

// Set true for running unminified/merged JS, false to run minified JS.  Don't forget to reminify your JS!
defined("Bootsole\JS_DEV")
	or define("Bootsole\JS_DEV", true);
    
?>
