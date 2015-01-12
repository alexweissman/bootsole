<?php

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
            throw new Exception("No component with name '$name' exists!");
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
        if (is_a($content, "NavComponentBuilder")){
            return $content;                               // NavComponentBuilder passed in
        } else {
            // If the content specifies a "@type" field, create the corresponding NavComponentBuilder object
            if (isset($content['@type'])){
                $type = $content['@type'];
                if ($type == "nav"){                // NavBuilder
                    // Attempt to construct a NavBuilder object.  Must have an "@items" field.
                    if (!isset($content['@items']))
                        throw new Exception("nav components must have a corresponding '@items' field.");
                        
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
                    throw new Exception("Unknown navbar component type '$type'.");
                }
            } else
                throw new Exception("Navbar components must be of type 'NavComponentBuilder', or specify a '@type' field.");
        }  
    }
    
    // Set components and render
    public function render(){
        $this->setContent('_components', $this->_components);
        return parent::render();
    }
}

// A generic nav component.  Should be abstract.

class NavComponentBuilder extends HtmlBuilder {
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
    }
    
    public function align($align){
        if ($align == "inherit"){
            $this->_align = "";
        } else if ($align == "left"){
            $this->_align = "navbar-left";
        } else if ($align == "right"){
            $this->_align = "navbar-right";
        } else {
            throw new Exception("align must be either 'left', 'right', or 'inherit'.");
        }
        return $this;
    }
    
    // Set align and render
    public function render(){
        $this->setContent('_align', $this->_align);
        return parent::render();
    }    
}

/* Builds a navbar item group, using the following magic fields:
    @items
*/

class NavBuilder extends NavComponentBuilder {
    
    protected $_items = [];
        
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<ul class='nav navbar-nav {{_align}}'>{{_items}}</ul>");   // Hardcoded template for now
        }
        
        // If @items set, add them
        if (isset($content['@items'])){
            foreach ($content['@items'] as $name => $item){
                $this->_items[$name] = $this->parseItem($item);
            }
        } 
    }
    
    // Get a particular item by name
    public function getItem($name){
        if (!isset($this->_items[$name]))
            throw new Exception("No item with name '$name' exists!");
        return $this->_items[$name];
    }
    
    // Add a nav item
    public function addItem($name, $content){
        $item = $this->parseItem($content);
        $this->_items[$name] = $item;
        return $item;
    }
    
    // Parse a nav item, either as an array or as a NavItemBuilder object
    private function parseItem($content){
        if (is_a($content, "NavItemBuilder")){
            $item = $content;                               // NavItemBuilder passed in
        } else if (isset($content['@items'])) {             // If the array specifies an "items" field, create a NavDropdownBuilder object
            $item = new NavDropdownBuilder($content);
        } else
            $item = new NavItemBuilder($content);       // Array of fields passed in
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
        $this->setContent('_items', $this->_items);
        return parent::render();
    }        
    
}

/* Builds a nav item, using the following default fields:
    active
    url
    label
*/

class NavItemBuilder extends HtmlBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<li class='{{active}}'><a href='{{url}}'>{{label}}</a></li>");
        }
        
        // Initialize active if not passed in
        if (!isset($content['active']))
            $this->setContent("active", "");                
    }      

    public function active($active){
        if ($active){
            $this->_content['active'] = "active";
        } else {
            $this->_content['active'] = "";
        }
    } 
}

/* Builds a navbar dropdown group, using the following magic fields:
    @items
*/

class NavDropdownBuilder extends NavItemBuilder {
    
    protected $_items = [];
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("
                <li class='{{active}} dropdown'>
                    <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false'>
                        {{label}} <span class='caret'></span>
                    </a>
                    <ul class='dropdown-menu {{_align}}' role='menu'>{{_items}}</ul>
                </li>");
        }
        
        // If @items set, add them
        if (isset($content['@items'])){
            foreach ($content['@items'] as $name => $item){
                $this->_items[$name] = $this->parseItem($item);
            }
        } 
    }

    // Get a particular item by name
    public function getItem($name){
        if (!isset($this->_items[$name]))
            throw new Exception("No item with name '$name' exists!");
        return $this->_items[$name];
    }
    
    // Add a dropdown item
    public function addItem($name, $content){
        $item = $this->parseItem($content);
        $this->_items[$name] = $item;
        return $item;
    }
    
    // Parse a nav item, either as an array or as a NavItemBuilder object
    private function parseItem($content){
        if (is_a($content, "NavItemBuilder")){
            $item = $content;                               // NavItemBuilder passed in
        } else
            $item = new NavItemBuilder($content);       // Array of fields passed in
        return $item;
    }

    // Set items and render
    public function render(){
        $this->setContent('_items', $this->_items);
        return parent::render();
    }       
}

class NavFormBuilder extends NavComponentBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<div class='navbar-form {{_align}}'>{{form}}</div>");   // Hardcoded template for now
        }
    }
}

class NavTextBuilder extends NavComponentBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<p class='navbar-text {{_align}}'>{{text}}</p>");   // Hardcoded template for now
        }
    }
}

class NavButtonBuilder extends NavComponentBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<button type='button' class='btn {{styles}} navbar-btn {{_align}}'>{{label}}</button>");   // Hardcoded template for now
        }
    }
}

class NavLinkBuilder extends NavComponentBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<p class='navbar-text {{_align}}'><a href='{{url}}' class='navbar-link'>{{text}}</a></p>");   // Hardcoded template for now
        }
    }
}

class DropdownBuilder extends HtmlBuilder {    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options = []);
        else {
            parent::__construct($content, null, $options = []);
            parent::setTemplate("<ul class='dropdown-menu {{_position}}' role='menu'>{{_items}}</ul>");
        }
        
        // If a '@position' field is specified, parse it
        if (isset($content['@position'])){
            $this->position($content['@position']);
        }    
    }
    
    
    public function align($position){
        if ($position == "inherit"){
            $this->_content['_position'] = "";
        } else if ($position == "left"){
            $this->_content['_position'] = "dropdown-menu-left";
        } else if ($position == "right"){
            $this->_content['_position'] = "dropdown-menu-right";
        } else {
            throw new Exception("position must be either 'left', 'right', or 'inherit'.");
        }
        return $this;
    }
    
    public function addDivider(){
        $this->_content['items'][] = "<li class='divider'></li>";
        return $this;
    }
}

?>
