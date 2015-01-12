<?php

/* Builds a page from a template, using the following magic fields:
    @header
    @footer
    
    The default page uses the following fields:
    main-nav
    heading-main
    content
*/

class PageBuilder extends HtmlBuilder {

    // The name of this page.  Used to refer to the page in the includes manifest.
    protected $_page_name = "";

    public function __construct($page_name, $content = [], $template_file = null, $options = []){
        $this->_page_name = $page_name;
        // Load the specified template, or the default page template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "pages/page-default.html", $options);
        
        // If 'header' is specified, load the css includes for the header
        if (isset($this->_content['header'])){
            $this->_content['header']->setContent("_css_includes", $this->loadCSSIncludes());
        }
        
        // If 'footer' is specified, load the js includes for the footer
        if (isset($this->_content['footer'])){
            $this->_content['footer']->setContent("_js_includes", $this->loadJSIncludes());
        }
    }
    
    public function header($content){
        $this->setContent("header", $content);
    }

    public function footer($content){
        $this->setContent("footer", $content);
    }
        
    public function setContent($placeholder, $content){    
        $new_content = parent::setContent($placeholder, $content);
        // If we're setting the "header" field, automatically load css includes
        if ($placeholder == "header")
            $new_content->setContent("_css_includes", $this->loadCSSIncludes());
        // If we're setting the "footer" field, automatically load js includes
        if ($placeholder == "footer")
            $new_content->setContent("_js_includes", $this->loadJSIncludes());        

    }
       
    // Get the appropriate CSS includes for this page
    private function loadCSSIncludes(){
        // Load manifest group for the page
        $manifest_group = $this->loadIncludeManifest();
            
        $site_path = PUBLIC_ROOT;    
        $result = "";
        
        // Ok, either load list of CSS files if CSS_DEV is enabled, or the minified CSS file if we are in production mode
        if (CSS_DEV){
            foreach ($manifest_group['css'] as $include){
                $result .= "<link href='{$site_path}$include' rel='stylesheet'>\n";
            }    
        } else {
            $result .= "<link href='{$site_path}{$manifest_group['min_css']}' rel='stylesheet'>\n";
        }
    
        return $result;
    }
    
    // Get the appropriate JS includes for this page
    private function loadJSIncludes(){
        // Load manifest group for the page
        $manifest_group = $this->loadIncludeManifest();
            
        $site_path = PUBLIC_ROOT;    
        $result = "";
        
        // Ok, either load list of JS files if JS_DEV is enabled, or the minified JS file if we are in production mode
        if (JS_DEV){
            foreach ($manifest_group['js'] as $include){
                $result .= "<script src='{$site_path}$include'></script>\n";
            }    
        } else {
            $result .= "<script src='{$site_path}{$manifest_group['min_js']}'></script>\n";
        }
    
        return $result;
    }
    
    // Load the include manifest object for this page
    private function loadIncludeManifest(){
        $manifest_file = PAGE_INCLUDES_SCHEMA_PATH;
            
        // Load the include manifest
        $manifest = json_decode(file_get_contents($manifest_file, FILE_USE_INCLUDE_PATH),true);
        if ($manifest === null)
            error_log(json_last_error());
    
        // Find the page in the JSON include manifest    
        foreach ($manifest as $name => $manifest_group){
            if (in_array($this->_page_name, $manifest_group['files'])){
                return $manifest_group;
            }
        }
        
        // Load default manifest if specified page not found
        return $manifest['default'];
    }
}

?>