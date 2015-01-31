<?php

namespace Bootsole;

/* Builds a navbar from a template, using the following magic fields:
    @components
    
    The default navbar also uses the following fields:
    brand_url
    brand_label
*/


class NavbarBuilder extends HtmlBuilder {

    protected $_components = [];

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "pages/navs/main-nav-default.html", $options);
            
        // Check if @components has been specified
        if (isset($content['@components'])){
            foreach ($content['@components'] as $name => $component){
                $this->_components[$name] = $this->parseComponent($component);
            }
        }
    }  
    
    // Get a particular component by name
    public function getComponent($name){
        if (!isset($this->_components[$name]))
            throw new \Exception("No component with name '$name' exists!");
        return $this->_components[$name];
    }
    
    // Add a new component
    public function addComponent($name, $content){    
        $component = $this->parseComponent($content);
        $this->_components[$name] = $component;
        return $component;        
    }    
    
    // Parse a NavbarBuilder component
    private function parseComponent($content){
        if (is_a($content, "Bootsole\NavComponentBuilder")){
            return $content;                               // NavComponentBuilder passed in
        } else {
            // If the content specifies a "@type" field, create the corresponding NavComponentBuilder object
            if (isset($content['@type'])){
                $type = $content['@type'];
                if ($type == "nav"){                // NavBuilder
                    // Attempt to construct a NavBuilder object.  Must have an "@items" field.
                    if (!isset($content['@items']))
                        throw new \Exception("nav components must have a corresponding '@items' field.");
                        
                    $item = new NavBuilder($content);
                    return $item;
                } else if ($type == "form"){        // NavFormBuilder
                    $item = new NavFormBuilder($content);
                    return $item;                
                } else if ($type == "text"){        // NavTextBuilder
                    $item = new NavTextBuilder($content);
                    return $item;                  
                } else if ($type == "button"){      // NavButtonBuilder
                    $item = new NavButtonBuilder($content);
                    return $item;                  
                } else if ($type == "link"){        // NavLinkBuilder
                    $item = new NavLinkBuilder($content);
                    return $item;                     
                } else {
                    throw new \Exception("Unknown navbar component type '$type'.");
                }
            } else
                throw new \Exception("Navbar components must be of type 'NavComponentBuilder', or specify a '@type' field.");
        }  
    }
    
    // Set components and render
    public function render(){
        $this->setContent('_components', $this->_components);
        return parent::render();
    }
}

// A generic nav component.

abstract class NavComponentBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;
    
    protected $_align = "";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
        }

        // Set the alignment of this component, if specified.
        if (isset($content['@align'])){
            $this->align($content['@align']);
        }
        
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }        
    }
    
    public function align($align){
        if ($align == "inherit"){
            $this->_align = "";
        } else if ($align == "left"){
            $this->_align = "navbar-left";
        } else if ($align == "right"){
            $this->_align = "navbar-right";
        } else {
            throw new \Exception("align must be either 'left', 'right', or 'inherit'.");
        }
        return $this;
    }
    
    // Set align and render
    public function render(){
        $this->setContent('_align', $this->_align);
        $this->setContent('_css_classes', $this->renderCssClasses());
        $this->setContent('_data', $this->renderDataAttributes());  
        return parent::render();
    }    
}

/* Builds a navbar item group, using the following magic fields:
    @items
*/

class NavBuilder extends NavComponentBuilder {
    
    use ItemCollection;
        
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<ul class='nav navbar-nav {{_align}} {{_css_classes}}' {{_data}}>{{_items}}</ul>");   // Hardcoded template for now
        }
        
        // If @items set, add them
        if (isset($content['@items'])){
            foreach ($content['@items'] as $name => $item){
                $this->_items[$name] = $this->parseItem($item);
            }
        } 
    }
    
    // Parse a nav item, either as an array or as a MenuItemBuilder object
    private function parseItem($content){
        if (is_a($content, "Bootsole\MenuItemBuilder")){
            $item = $content;                               // MenuItemBuilder passed in
        } else if (isset($content['@items'])) {             // If the array specifies an "items" field, create a NavDropdownBuilder object
            $item = new NavDropdownBuilder($content);
        } else
            $item = new MenuItemBuilder($content);       // Array of fields passed in
        return $item;
    }
        
    // Set the active menu item
    public function setActiveItem($name){
        foreach ($this->_items as $item){
            $item->active(false);
        }
        $this->_items[$name]->active(true);
        return $this->_items[$name];
    }
    
    // Set items and render
    public function render(){
        $this->setContent('_items', $this->renderItems());
        return parent::render();
    }        
    
}

/* Builds a navbar dropdown group, using the following magic fields:
    @dropdown
*/

class NavDropdownBuilder extends MenuItemBuilder {
    protected $_dropdown;   // The DropdownBuilder representing the list of items

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("
                <li class='dropdown {{_active}} {{_disabled}}'>
                    <a href='#' class='dropdown-toggle {{_css_classes}}' data-toggle='dropdown' {{_data}} role='button' aria-expanded='false'>
                        {{_label}} <span class='caret'></span>
                    </a>
                   {{_dropdown}}
                </li>");
        }
        
        // Set the dropdown directly from the content
        $this->dropdown($content);
    }

    public function dropdown($dropdown){
        $this->_dropdown = $this->parseDropdown($dropdown);
    }
    
    private function parseDropdown($content){
        if (is_a($content, "Bootsole\DropdownBuilder")){
            return $content;                               // DropdownBuilder passed in
        } else {
            $dropdown = new DropdownBuilder($content);
            return $dropdown;
        }
    }

    // Set items and render
    public function render(){
        $this->setContent('_dropdown', $this->_dropdown);
        return parent::render();
    }       
}

class NavFormBuilder extends NavComponentBuilder {

    protected $_form;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<div class='navbar-form {{_align}} {{_css_classes}}' {{_data}}>{{_form}}</div>");   // Hardcoded template for now
        }
        
        if (isset($content['@form'])){
            $this->form($content['@form']);
        }        
    }

    public function form($form){
        $this->_form = $form;
    }
    
    public function render(){
        $this->setContent("_form", $this->_form);
        return parent::render();
    }
}

class NavTextBuilder extends NavComponentBuilder {

    protected $_text;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<p class='navbar-text {{_align}} {{_css_classes}}' {{_data}}>{{_text}}</p>");   // Hardcoded template for now
        }
        
        if (isset($content['@text'])){
            $this->text($content['@text']);
        }        
    }

    public function text($text){
        $this->_text = $text;
    }
    
    public function render(){
        $this->setContent("_text", $this->_text);
        return parent::render();
    }    
}

class NavButtonBuilder extends NavComponentBuilder {

    protected $_label;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<button type='button' class='btn navbar-btn {{_align}} {{_css_classes}}' {{_data}}>{{_label}}</button>");   // Hardcoded template for now
        }
        
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }        
    }
    
    public function label($label){
        $this->_label = $label;
    }
    
    public function render(){
        $this->setContent("_label", $this->_label);
        return parent::render();
    }    
}

class NavLinkBuilder extends NavComponentBuilder {

    protected $_label;
    protected $_url;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<p class='navbar-text {{_align}}'><a href='{{_url}}' class='navbar-link {{_css_classes}}' {{_data}}>{{_label}}</a></p>");   // Hardcoded template for now
        }
        
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }
        
        if (isset($content['@url'])){
            $this->url($content['@url']);
        }        
    }
    
    public function label($label){
        $this->_label = $label;
    }       
 
    public function url($url){
        $this->_url = $url;
    }
    
    public function render(){
        $this->setContent("_label", $this->_label);
        $this->setContent("_url", $this->_url);
        return parent::render();
    }        
}

?>
