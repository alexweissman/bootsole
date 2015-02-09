<?php

namespace Bootsole;

/* Builds a page from a template, using the following magic fields:
    @name
    @header
    @footer
    @schema
    
    The default page uses the following fields:
    main-nav
    heading-main
    content
*/

class PageBuilder extends HtmlBuilder {

    
    protected $_name = "";      // The name of this page.  Used to refer to the page in the includes schema.
    protected $_schema = null;   // The path of the schema to be used for this page.  Will be set to the schema PAGE_INCLUDES_SCHEMA_PATH if not specified.
    protected $_header;     // PageHeaderBuilder object
    protected $_footer;     // PageFooterBuilder object

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default page template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "pages/page-default.html", $options);
        
        // If '@name' is specified, set it.
        if (isset($content['@name'])){
            $this->_name = $content['@name'];
        }        

        // If '@schema' is specified, set it.
        if (isset($content['@schema'])){
            $this->_schema = $content['@schema'];
        }           
        
        // If '@header' is specified, set it.  Otherwise, load a default header.
        if (isset($content['@header'])){
            $this->_header = $this->parseHeader($content['@header']);
        } else {
            $this->_header = new PageHeaderBuilder();
        }
        
        // If '@footer' is specified, set it.  Otherwise, load a default footer.
        if (isset($content['@footer'])){
            $this->_footer = $this->parseFooter($content['@footer']);
        } else {
            $this->_footer = new PageFooterBuilder();
        }
    }
    
    // Set the page name
    public function name($content){
        $this->_name = $content;
    }    
    
    // Set the schema
    public function schema($content){
        $this->_schema = $content;
    } 
    
    // Set the page header
    public function header($content){
        $this->_header = $this->parseHeader($content);
    }

    // Set the page footer
    public function footer($content){
        $this->_footer = $this->parseFooter($content);
    }
    
    private function parseHeader($content){
        // If $content is already a 'PageHeaderBuilder' object, then just return it.  Otherwise, construct it.
        if (is_a($content, "Bootsole\PageHeaderBuilder")){
            return $content;
        } else {
            $header = new PageHeaderBuilder($content);
            return $header;
        }
    }

    private function parseFooter($content){
        // If $content is already a 'PageFooterBuilder' object, then just return it.  Otherwise, construct it.
        if (is_a($content, "Bootsole\PageFooterBuilder")){
            return $content;
        } else {
            $footer = new PageFooterBuilder($content);
            return $footer;
        }
    }

    // Load page manifest, set css and js, and render
    public function render(){
        $manifest_group = PageSchema::load($this->_name, $this->_schema);
        $this->_header->css_includes($manifest_group);
        $this->_content['_header'] = $this->_header;
        $this->_footer->js_includes($manifest_group);
        $this->_content['_footer'] = $this->_footer;
        return parent::render();
    }    
}

/* Builds a page header (<head> block), using the following magic fields:
    @css_includes
    
*/
class PageHeaderBuilder extends HtmlBuilder {
    
    protected $_css_includes;       // An array containing 'css', an array of strings representing paths to CSS include files, and 'min_css', a path to the minified version of the CSS
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default page template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "pages/headers/header-default.html", $options);
    
        // If '@css_includes' is specified, set it.
        if (isset($content['@css_includes'])){
            $this->_css_includes = $content['@css_includes'];
        }   
    }
    
    public function css_includes($content){
        $this->_css_includes = $content;
    }
           
    public function render(){
        if (defined('Bootsole\URI_CSS_ROOT'))
            $site_path = URI_CSS_ROOT;
        else
            $site_path = "";
            
        $this->_content['_css_includes'] = "";
        
        // Ok, either load list of CSS files if CSS_DEV is enabled, or the minified CSS file if we are in production mode
        if (!defined('Bootsole\CSS_DEV') || CSS_DEV){
            foreach ($this->_css_includes['css'] as $include){
                $this->_content['_css_includes'] .= "<link href='{$site_path}$include' rel='stylesheet'>\n";
            }    
        } else {
            $this->_content['_css_includes'] .= "<link href='{$site_path}{$this->_css_includes['min_css']}' rel='stylesheet'>\n";
        }    
        return parent::render();
    }
}

/* Builds a page footer (<footer> block), using the following magic fields:
    @js_includes
    
*/
class PageFooterBuilder extends HtmlBuilder {

    protected $_js_includes;       // An array containing 'js', an array of strings representing paths to JS include files, and 'min_js', a path to the minified version of the JS
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default footer template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "pages/footers/footer-default.html", $options);
    
        // If '@js_includes' is specified, set it.
        if (isset($content['@js_includes'])){
            $this->_js_includes = $content['@js_includes'];
        }   
    }
    
    public function js_includes($content){
        $this->_js_includes = $content;
    }
           
    public function render(){
        if (defined('Bootsole\URI_JS_ROOT'))
            $site_path = URI_JS_ROOT;
        else
            $site_path = "";
        $this->_content['_js_includes'] = "";
        
        // Ok, either load list of JS files if JS_DEV is enabled, or the minified JS file if we are in production mode
        if (!defined('Bootsole\JS_DEV') || JS_DEV){
            foreach ($this->_js_includes['js'] as $include){
                $this->_content['_js_includes'] .= "<script src='{$site_path}$include'></script>\n";
            }    
        } else {
            $this->_content['_js_includes'] .= "<script src='{$site_path}{$this->_js_includes['min_js']}'></script>\n";
        }
     
        return parent::render();
    }
}
   
/* A static class used to load an includes schema for a given page. */
    
class PageSchema {
    
    // Load the include schema object for the specified page
    public static function load($page_name, $schema_path = null){
        // Set default schema file, if not specified.
        if (!$schema_path)
            $schema_path = FILE_SCHEMA_PAGE_DEFAULT;
            
        // Load the include manifest
        $schema = json_decode(file_get_contents($schema_path, FILE_USE_INCLUDE_PATH),true);
        if ($schema === null)
            throw new \Exception("Could not load schema file '$schema_path'.");
    
        // Find the page in the JSON include manifest    
        foreach ($schema as $name => $manifest_group){
            if (in_array($page_name, $manifest_group['pages'])){
                return $manifest_group;
            }
        }
        
        // Load default manifest if specified page not found
        return $schema['default'];
    }
}

?>