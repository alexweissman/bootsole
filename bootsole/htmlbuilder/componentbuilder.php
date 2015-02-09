<?php

namespace Bootsole;

/* A button group. */

class ButtonGroupBuilder extends HtmlBuilder {
    
    use HtmlAttributesBuilder, ItemCollection;
        
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("
            <div class='btn-group {{_css_classes}}' {{_data}}>
                {{_items}}
            </div>");   // Hardcoded template for now
        }
        
        // If @items set, add them
        if (isset($content['@items'])){
            foreach ($content['@items'] as $name => $item){
                $this->_items[$name] = $this->parseItem($item);
            }
        }
        
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }        
    }
    
    // Button groups can contain ButtonBuilder, DropdownButtonBuilder, and ButtonDropdownAddonBuilder objects
    private function parseItem($content){
        if (is_a($content, "Bootsole\ButtonBuilder") || is_a($content, "Bootsole\DropdownButtonBuilder") || is_a($content, "Bootsole\ButtonDropdownAddonBuilder")){
            $item = $content;                              
        } else if (isset($content['@items'])) {             
            // If no label, create a ButtonDropdownAddonBuilder object
            if (isset($content['@label']))
                $item = new DropdownButtonBuilder($content);
            else                
                $item = new ButtonDropdownAddonBuilder($content);
        } else
            $item = new ButtonBuilder($content);       // Array of fields passed in
        return $item;
    }
           
    // Set items and render
    public function render(){
        $this->setContent('_items', $this->renderItems());
        $this->setContent('_css_classes', $this->renderCssClasses());
        $this->setContent('_data', $this->renderDataAttributes());
        
        return parent::render();
    }     
    
}

/* Represents a basic button */

class ButtonBuilder extends HtmlBuilder {    
    use HtmlAttributesBuilder;
    
    protected $_type = "button";
    protected $_name = "";
    protected $_label = "";
    protected $_active = false;
    protected $_display = "";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default button template
        if ($template_file)
            parent::__construct($content, $template_file, $options = []);
        else {
            parent::__construct($content, null, $options = []);
            parent::setTemplate("            
                <button type='{{_type}}' name='{{_name}}' class='btn {{_css_classes}} {{_active}}' {{_display}} {{_data}}>
                    {{_label}}
                </button>");
        } 

        if (isset($content['@type'])){
            $this->type($content['@type']);
        }        

        if (isset($content['@label'])){
            $this->label($content['@label']);
        }

        if (isset($content['@name'])){
            $this->name($content['@name']);
        }        

        // Initialize @display if passed in
        if (isset($content['@display']))
            $this->display($content['@display']);

        // Initialize @active if passed in
        if (isset($content['@active']))
            $this->active($content['@active']);
            
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }

    }    

    public function type($type){
        $this->_type = $type;
    }
    
    public function active($active){
        if (is_bool($active)) {
            $this->_active = $active;
        }
        else {
            switch(strtolower($active)){
                case "true":
                case "1": $this->_active = true;  break;     
                case "false":
                case "0": $this->_active = false; break;            
                default: throw new \Exception("'active' must be a boolean value.");
            }
        }
    }

    public function label($label){
        $this->_label = $label;
    }
    
    public function display($display){
        switch($display){
            case "show":        
            case "hidden":  
            case "disabled":    $this->_display = $display; break;
            default:            throw new \Exception("display must be 'show', 'hidden', or 'disabled'.");
        }
    }

    public function name($name){
        $this->_name = $name;
    }        
    
    // Set contents and render
    public function render(){
        if ($this->_display == "hidden")
            return "";
    
        $this->setContent('_name', $this->_name);
        $this->setContent('_label', $this->_label);    
        $this->setContent('_active', $this->_active ? "active" : "");
        $this->setContent('_display', $this->_display);
        
        // Add data attributes for special button types
        if ($this->_type == "submit") {
            $this->setContent('_type', "submit");
            $this->dataAttribute("loading-text", "Please wait...");
        } else if ($this->_type == "launch") {
            $this->setContent('_type', "button");
            $this->dataAttribute("toggle", "modal");
        } else if ($this->_type == "cancel") {
            $this->setContent('_type', "button");
            $this->dataAttribute("dismiss", "modal");
        } else
            $this->setContent('_type', "button");
            
        $this->setContent('_css_classes', $this->renderCssClasses());
        $this->setContent('_data', $this->renderDataAttributes());
        
        return parent::render();
    }

}

class DropdownButtonBuilder extends ButtonBuilder {
    
    protected $_dropdown;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default button template
        if ($template_file)
            parent::__construct($content, $template_file, $options = []);
        else {
            parent::__construct($content, null, $options = []);
            parent::setTemplate("            
                <div class='btn-group'>
                    <button type='{{_type}}' name='{{_name}}' class='btn {{_css_classes}} {{_active}} dropdown-toggle' {{_disabled}} {{_data}} data-toggle='dropdown' aria-expanded='false'>
                        {{_label}} <span class='caret'></span>
                    </button>
                    {{_dropdown}}
                </div>");
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

class ButtonDropdownAddonBuilder extends DropdownButtonBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default button template
        if ($template_file)
            parent::__construct($content, $template_file, $options = []);
        else {
            parent::__construct($content, null, $options = []);
            parent::setTemplate("
                    <button type='{{_type}}' name='{{_name}}' class='btn {{_css_classes}} {{_active}} dropdown-toggle' {{_disabled}} {{_data}} data-toggle='dropdown' aria-expanded='false'>
                        <span class='caret'></span>
                    </button>
                    {{_dropdown}}
                ");
        }
    }
}


/* Represents a basic dropdown menu. */

class DropdownBuilder extends HtmlBuilder {    
    use ItemCollection;
    
    protected $_align = "inherit";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options = []);
        else {
            parent::__construct($content, null, $options = []);
            parent::setTemplate("
            <ul class='dropdown-menu {{_align}}' role='menu'>
                {{_items}}
            </ul>");
        }
        
        // If a '@align' field is specified, parse it
        if (isset($content['@align'])){
            $this->align($content['@align']);
        }    
    
        // If @items set, add them
        if (isset($content['@items'])){
            foreach ($content['@items'] as $name => $item){
                $this->_items[$name] = $this->parseItem($item);
            }
        }     
    }
    
    public function align($align){
        switch($align) {
            case "inherit":
            case "left":
            case "right": $this->_align = $align; break;
            default:
            throw new \Exception("align must be either 'left', 'right', or 'inherit'.");
        }
        return $this;
    }
    
    public function addDivider(){
        $this->_content['items'][] = "<li class='divider'></li>";
        return $this;
    }
    
    // Set alignment, items and render
    public function render(){
        if ($this->_align == "inherit"){
            $this->_content['_align'] = "";
        } else if ($this->_align == "left"){
            $this->_content['_align'] = "dropdown-menu-left";
        } else if ($this->_align == "right"){
            $this->_content['_align'] = "dropdown-menu-right";
        } else {
            throw new \Exception("position must be either 'left', 'right', or 'inherit'.");
        }
        
        $this->setContent('_items', $this->renderItems());
        return parent::render();
    }

    // Parse an item, either as an array or as a MenuItemBuilder object
    private function parseItem($content){
        if (is_a($content, "Bootsole\MenuItemBuilder")){
            $item = $content;                               // MenuItemBuilder passed in
        } else
            $item = new MenuItemBuilder($content);       // Array of fields passed in
        return $item;
    }    
}

/* Builds a generic menu item, using the following magic fields:
    @active    (nav items only)
    @display
    @url
    @label
*/

class MenuItemBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;

    protected $_label = "";
    protected $_url = "";
    protected $_display = "show";
    protected $_active = false;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            parent::setTemplate("<li class='{{_active}} {{_display}}'><a role='menuitem' class='{{_css_classes}}' {{_data}} href='{{_url}}'>{{_label}}</a></li>");
        }
        
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }

        if (isset($content['@url'])){
            $this->url($content['@url']);
        }
        
        // Initialize @display if passed in
        if (isset($content['@display']))
            $this->display($content['@display']);

        // Initialize @active if passed in
        if (isset($content['@active']))
            $this->active($content['@active']);
            
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }
    }
    
    public function active($active){
        if (is_bool($active)) {
            $this->_active = $active;
        }
        else {
            switch(strtolower($active)){
                case "true":
                case "1": $this->_active = true;  break;     
                case "false":
                case "0": $this->_active = false; break;            
                default: throw new \Exception("'active' must be a boolean value.");
            }
        }
    }

    public function label($label){
        $this->_label = $label;
    }
    
    public function url($url){
        $this->_url = $url;
    }  

    public function display($display){
        switch($display){
            case "show":        
            case "hidden":  
            case "disabled":    $this->_display = $display; break;
            default:            throw new \Exception("display must be 'show', 'hidden', or 'disabled'.");
        }
    }   
    
    // Set styles and render
    public function render(){
        // 'Hidden' items are simply not rendered at all
        if ($this->_display == "hidden")
            return "";
        
        $this->setContent('_label', $this->_label);
        $this->setContent('_url', $this->_url);       
        $this->setContent('_active', $this->_active ? "active" : "");
        $this->setContent('_display', ($this->_display == "disabled") ? "disabled" : "");
        $this->setContent('_css_classes', $this->renderCssClasses());
        $this->setContent('_data', $this->renderDataAttributes());
        return parent::render();
    }
    
}

/* Represents a generic collection of Items.  Every class that uses this trait must implement the parseItem method. */

trait ItemCollection {

    protected $_items = [];     // An array of HtmlBuilder objects.

    // Get a particular item by name
    public function getItem($name){
        if (!isset($this->_items[$name]))
            throw new \Exception("No item with name '$name' exists!");
        return $this->_items[$name];
    }
    
    // Add a dropdown item
    public function addItem($name, $content){
        $item = $this->parseItem($content);
        $this->_items[$name] = $item;
        return $item;
    }

    public function renderItems(){
        $result = "";
        foreach($this->_items as $name => $item){
            $result .= $item->render() . PHP_EOL;
        }
        return $result;
    }
}

?>