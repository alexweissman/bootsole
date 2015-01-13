<?php

class HtmlBuilder {

    protected $_options;       // Auxiliary options, directives, etc.
    protected $_template;      // The template (string) to be rendered.
    protected $_content = [];  // An array mapping each placeholder to its contents.  Each content item can be either a string, or another HtmlBuilder object.

    public function __construct($content = [], $template_file = null, $options = []) {
        $this->_options = $options;
        if (!isset($this->_options['show_missing_hooks'])){
            $this->_options['show_missing_hooks'] = OPTION_SHOW_MISSING_HOOKS;
        }
        // If a template file is specified, load that template.  Otherwise, load a blank template
        if ($template_file)
            $this->_template = $this->loadTemplate($template_file);
        else
            $this->_template = null;      

        // Parse the content.  
        foreach ($content as $placeholder => $child_content){
            // Skip any directives
            if (substr($placeholder, 0, 1) == "@"){
                continue;
            }
            $this->_content[$placeholder] = $this->parseContent($placeholder, $child_content);
        }  
    }
    
    // Set and automatically parse content for the specified placeholder
    public function setContent($placeholder, $content){
        return $this->_content[$placeholder] = $this->parseContent($placeholder, $content);
    }
    
    // Get a content member, if it exists.
    public function getContent($placeholder){
        if (isset($this->_content[$placeholder]))
            return $this->_content[$placeholder];
        else
            return null;
    }
    
    // Parse content.  Inline arrays of content get converted to objects automatically.
    public function parseContent($placeholder, $content){
        $result = "";        
     
        // If child is an HtmlBuilder object, just add it
        if (is_a($content, "HtmlBuilder")) {
            $result = $content;
        // If it is an array, then attempt to load a template specified by @source or @template and construct a new HtmlBuilder object 
        } else if (is_array($content)){
            $result = [];
            // Load template, if specified
            if(isset($content['@source'])){
                // read in source template
                $child_template = $this->loadTemplate($content['@source']);
            } else if(isset($content['@template'])){
                $child_template = $content['@template'];
            }
            // Array must have a '@content' or '@array' field, or be an array of HtmlBuilder objects.
            if (isset($content['@content'])){
                // Simple content fields
                $hb = new HtmlBuilder($content['@content']);
                $hb->setTemplate($child_template);
                $result = $hb;                
            } else if (isset($content['@array'])){
                // Loop through array, creating new HtmlBuilder for each element.  They will be concatenated upon rendering.
                $result = [];
                foreach($content['@array'] as $i => $row){
                   $hb = new HtmlBuilder($row);
                   $hb->setTemplate($child_template);
                   $result[] = $hb;
                }
                return $result;
            } else {
                // Check that every element is an HtmlBuilder object.  They will be concatenated upon rendering.
                foreach ($content as $i => $row){
                    if (!is_a($row, "HtmlBuilder"))
                        throw new Exception("The array assigned to placeholder '$placeholder' must contain a '@content' or '@array' field, or be an array of HtmlBuilder objects.");
                }
                $result = $content;
                return $result;
            }
        // If it is a scalar, just add it
        } else if (is_scalar($content)) {
            $result = $content;
        // Otherwise throw an exception
        } else {
            throw new Exception("The contents of '$placeholder' must be a scalar value, HtmlBuilder object, or subarray.");
        }

        return $result;
    }
    
    protected function loadTemplate($path){
        $template = file_get_contents(TEMPLATES_PATH . $path);
            
        //Check to see if we can access the file / it has some contents
        if(!$template || empty($template)) {
            throw new Exception("The template '$path' could not be loaded.");
        }
        
        return $template;
    }
    
    // Explicitly set the template (string)
    public function setTemplate($template){
        $this->_template = $template;
    }
    
    // Merges this object's content with additional content.  Anything in this object's content will take priority over the merged content.
    public function mergeContent($content){
        $this->_content = $this->_content + $content; 
    }
    
    public function render(){
        if (!$this->_template)
            throw new Exception("The template is missing for this object!");
        return $this->renderContent($this->_content, $this->_template);
    }
    
    /* The basic function for rendering a template, given a content array */
    public function renderContent($content, $template){        
        // 1. Find all hooks in the template
        preg_match_all('/{{.*?}}/', $template, $matches);
        
        $hooks = $matches[0];        
        $replacements = [];
        
        
        // For each hook, try to find it in the content array
        foreach($hooks as $hook){
            $hook = substr($hook, 2, -2);
        
            // Skip undefined hooks (either display placeholder, or replace with blank space)
            if (!isset($content[$hook])){
                error_log("The hook '$hook' has not been defined in the contents.");
                if (isset($this->_options['show_missing_hooks']) && $this->_options['show_missing_hooks'] == true)
                    $replacements[] = "{{$hook}}";
                else
                    $replacements[] = "";
                continue;
            }
            
            // Get hook content type
            $type = gettype($content[$hook]);
            
            $value = null;
            
            // Recursively process arrays of HtmlBuilder objects
            if ($type == "array"){
                $value = "";
                $elements = $content[$hook];
                foreach ($elements as $name => $obj){
                    $value .= $obj->render() . PHP_EOL;
                }
            } else if ($type == "object") {
                $value = $content[$hook]->render() . PHP_EOL;
            } else {
                $value = $content[$hook];
            }
        
            $replacements[] = $value;
                
        }
        
        return str_replace($hooks, $replacements, $template);     
    }

    public function print_r(){
        echo "<pre>";
        echo htmlspecialchars(print_r($this->_content, true));
        echo "</pre>";
        
    }
}

?>
